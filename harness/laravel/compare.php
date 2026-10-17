<?php

declare(strict_types=1);

/*
 * Compares reference PHP examples with the fixture application.
 *
 * Reads a JSON list of {label, code} from STDIN and prints a JSON object with
 * `errors` (members that differ from or are missing in the fixture), `members`
 * (fixture file and line range of every class member an example shows) and
 * `tests` (labels of examples that are Pest test files).
 */

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

require __DIR__.'/vendor/autoload.php';

$root = __DIR__;
$parser = new ParserFactory()->createForHostVersion();
$printer = new Standard();
$finder = new NodeFinder();

const NAMESPACE_PATHS = [
    'App\\' => 'app/',
    'Database\\Factories\\' => 'database/factories/',
    'Database\\Seeders\\' => 'database/seeders/',
    'Tests\\' => 'tests/',
];

/**
 * @return list<Node>
 */
function parse(string $code): array
{
    global $parser;

    $nodes = $parser->parse($code) ?? [];
    $traverser = new NodeTraverser(new NameResolver(null, ['replaceNodes' => false]));

    return $traverser->traverse($nodes);
}

function show(Node $node): string
{
    global $printer;

    return $printer->prettyPrint([$node]);
}

function fixturePath(string $class): null|string
{
    foreach (NAMESPACE_PATHS as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            return $directory.str_replace('\\', '/', mb_substr($class, mb_strlen($prefix))).'.php';
        }
    }

    return null;
}

function classLike(array $nodes): null|Stmt\ClassLike
{
    global $finder;

    $found = $finder->findFirstInstanceOf($nodes, Stmt\ClassLike::class);

    return $found instanceof Stmt\ClassLike ? $found : null;
}

/**
 * @return array<string, Node>
 */
function membersOf(Stmt\ClassLike $class): array
{
    $members = [];

    foreach ($class->stmts as $stmt) {
        $key = match (true) {
            $stmt instanceof Stmt\ClassMethod => 'method '.$stmt->name->toString(),
            $stmt instanceof Stmt\Property => 'property '.$stmt->props[0]->name->toString(),
            $stmt instanceof Stmt\ClassConst => 'constant '.$stmt->consts[0]->name->toString(),
            $stmt instanceof Stmt\EnumCase => 'case '.$stmt->name->toString(),
            $stmt instanceof Stmt\TraitUse => 'trait '.implode(',', array_map(
                fn (Node\Name $name): string => $name->toString(),
                $stmt->traits,
            )),
            default => null,
        };

        if ($key !== null) {
            $members[$key] = $stmt;
        }
    }

    return $members;
}

/**
 * @return list<string>
 */
function docLines(null|PhpParser\Comment\Doc $doc): array
{
    if ($doc === null) {
        return [];
    }

    preg_match_all('/@(?:property-read|property|mixin|extends|use)\s+[^\n]+/', $doc->getText(), $matches);

    return array_map(trim(...), $matches[0]);
}

function isSubsequence(array $needles, array $haystack): bool
{
    $position = 0;

    foreach ($needles as $needle) {
        $found = array_search($needle, array_slice($haystack, $position), true);

        if ($found === false) {
            return false;
        }

        $position += $found + 1;
    }

    return true;
}

/**
 * Array literal returned by a method or assigned to a property, as printed items.
 *
 * @return null|list<string>
 */
function arrayItems(Node $member): null|array
{
    global $finder;

    $array = match (true) {
        $member instanceof Stmt\ClassMethod => ($return = $finder->findFirstInstanceOf($member->stmts ?? [], Stmt\Return_::class))
            && $return->expr instanceof Array_ ? $return->expr : null,
        $member instanceof Stmt\Property => $member->props[0]->default instanceof Array_ ? $member->props[0]->default : null,
        default => null,
    };

    return $array === null ? null : array_map(fn (Node $item): string => show($item), $array->items);
}

/**
 * A relation method without conditions or inherited attributes needs no test.
 */
function isPlainRelation(Stmt\ClassMethod $method): bool
{
    $statements = $method->stmts ?? [];

    if (count($statements) !== 1 || ! $statements[0] instanceof Stmt\Return_) {
        return false;
    }

    $call = $statements[0]->expr;

    while ($call instanceof Node\Expr\MethodCall && in_array($call->name->toString(), ['as', 'using', 'withAttributes', 'withPivot', 'withTimestamps'], true)) {
        $call = $call->var;
    }

    return $call instanceof Node\Expr\MethodCall
        && $call->var instanceof Node\Expr\Variable
        && $call->var->name === 'this'
        && in_array($call->name->toString(), ['belongsTo', 'belongsToMany', 'hasMany', 'hasOne', 'morphMany', 'morphTo'], true);
}

function compareClass(string $label, Stmt\ClassLike $block, array &$errors, array &$members): void
{
    global $root;

    $name = $block->namespacedName?->toString() ?? $block->name?->toString();
    $path = fixturePath($name);

    if ($path === null || ! is_file($root.'/'.$path)) {
        $errors[] = "$label: no fixture file for $name";

        return;
    }

    $fixture = classLike(parse(file_get_contents($root.'/'.$path)));

    if ($fixture === null || get_class($fixture) !== get_class($block)) {
        $errors[] = "$label: fixture $path does not declare the same kind of $name";

        return;
    }

    if ($block instanceof Stmt\Class_ && $fixture instanceof Stmt\Class_) {
        if (show($block->extends ?? new Node\Name('none')) !== show($fixture->extends ?? new Node\Name('none'))) {
            $errors[] = "$label: $name extends differs from the fixture";
        }

        $missing = array_diff(
            array_map(show(...), $block->implements),
            array_map(show(...), $fixture->implements),
        );

        if ($missing !== []) {
            $errors[] = "$label: $name fixture lacks interfaces ".implode(', ', $missing);
        }
    }

    $missing = array_diff(array_map(show(...), $block->attrGroups), array_map(show(...), $fixture->attrGroups));

    if ($missing !== []) {
        $errors[] = "$label: $name fixture lacks attributes ".implode(', ', $missing);
    }

    if (! isSubsequence(docLines($block->getDocComment()), docLines($fixture->getDocComment()))) {
        $errors[] = "$label: $name docblock lines are missing or ordered differently in the fixture";
    }

    $fixtureMembers = membersOf($fixture);

    foreach (membersOf($block) as $key => $member) {
        $other = $fixtureMembers[$key] ?? null;

        if ($other === null) {
            $errors[] = "$label: $name fixture lacks $key";

            continue;
        }

        $items = arrayItems($member);
        $otherItems = arrayItems($other);

        if (in_array($key, ['method casts', 'property attributes'], true) && $items !== null && $otherItems !== null) {
            if (! isSubsequence($items, $otherItems)) {
                $errors[] = "$label: $name $key items are missing or ordered differently in the fixture";
            }
        } elseif (show($member) !== show($other)) {
            $errors[] = "$label: $name $key differs from the fixture";
        }

        if ($member instanceof Stmt\ClassMethod) {
            $members[] = [
                'end' => $other->getEndLine(),
                'label' => $label,
                'member' => "$name::".$member->name->toString(),
                'path' => $path,
                'plain_relation' => isPlainRelation($other),
                'start' => $other->getStartLine(),
            ];
        }
    }
}

/**
 * @return array<string, list<string>>
 */
function migrationStatements(array $nodes): array
{
    global $finder;

    $statements = ['*' => []];
    $up = $finder->findFirst($nodes, fn (Node $node): bool => $node instanceof Stmt\ClassMethod
        && $node->name->toString() === 'up');

    foreach ($up?->stmts ?? [] as $stmt) {
        $call = $stmt instanceof Stmt\Expression ? $stmt->expr : null;

        if ($call instanceof StaticCall && $call->name instanceof Node\Identifier
            && $call->name->toString() === 'create' && isset($call->args[1])) {
            $table = show($call->args[0]->value);
            $closure = $call->args[1]->value;
            $statements[$table] = array_map(show(...), $closure->stmts ?? []);
        } else {
            $statements['*'][] = show($stmt);
        }
    }

    return $statements;
}

function compareMigration(string $label, array $nodes, array &$errors): void
{
    global $root;

    $fixture = ['*' => []];

    foreach ([...glob($root.'/database/migrations/*.php'), ...glob($root.'/tests/migrations/*.php')] as $file) {
        foreach (migrationStatements(parse(file_get_contents($file))) as $table => $statements) {
            $fixture[$table] = [...($fixture[$table] ?? []), ...$statements];
        }
    }

    foreach (migrationStatements($nodes) as $table => $statements) {
        foreach ($statements as $statement) {
            $normalized = array_map(fn (string $code): string => preg_replace('/\s+/', ' ', $code), $fixture[$table] ?? []);

            if (! in_array(preg_replace('/\s+/', ' ', $statement), $normalized, true)) {
                $first = strtok($statement, "\n");
                $errors[] = "$label: fixture migration for $table lacks: $first";
            }
        }
    }
}

/**
 * Translation files must match a fixture file exactly. A configuration example
 * may show only some sections of a fixture file, in the same order.
 */
function compareReturnFile(string $label, array $nodes, array &$errors): void
{
    global $finder, $root;

    $parts = function (array $nodes) use ($finder): array {
        $return = $finder->findFirstInstanceOf($nodes, Stmt\Return_::class);
        $items = $return?->expr instanceof Array_ ? array_map(show(...), $return->expr->items) : [];
        $rest = array_map(show(...), array_filter($nodes, fn (Node $node): bool => ! $node instanceof Stmt\Return_));

        return [$rest, $items];
    };

    [$rest, $items] = $parts($nodes);

    foreach (glob($root.'/lang/*/*.php') as $file) {
        if ($parts(parse(file_get_contents($file))) === [$rest, $items]) {
            return;
        }
    }

    foreach (glob($root.'/config/*.php') as $file) {
        [$fixtureRest, $fixtureItems] = $parts(parse(file_get_contents($file)));

        if ($fixtureRest === $rest && $items !== [] && isSubsequence($items, $fixtureItems)) {
            return;
        }
    }

    $errors[] = "$label: no fixture translation or configuration file matches it";
}

$examples = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$errors = [];
$members = [];
$tests = [];

foreach ($examples as ['label' => $label, 'code' => $code]) {
    try {
        $nodes = parse($code);
    } catch (PhpParser\Error $error) {
        $errors[] = "$label: ".$error->getMessage();

        continue;
    }

    $class = classLike($nodes);
    $isTest = $finder->findFirst($nodes, fn (Node $node): bool => $node instanceof Node\Expr\FuncCall
        && $node->name instanceof Node\Name && in_array($node->name->toString(), ['it', 'test'], true)) !== null;

    if ($isTest) {
        $tests[] = $label;
    } elseif ($class instanceof Stmt\Class_ && $class->name === null
        && $class->extends?->getAttribute('resolvedName')?->toString() === 'Illuminate\\Database\\Migrations\\Migration') {
        compareMigration($label, $nodes, $errors);
    } elseif ($class !== null) {
        compareClass($label, $class, $errors, $members);
    } elseif ($finder->findFirstInstanceOf($nodes, Stmt\Return_::class) !== null) {
        compareReturnFile($label, $nodes, $errors);
    } else {
        $errors[] = "$label: example is neither a class, a migration, a returned array nor a test file";
    }
}

echo json_encode(['errors' => $errors, 'members' => $members, 'tests' => $tests], JSON_PRETTY_PRINT);
