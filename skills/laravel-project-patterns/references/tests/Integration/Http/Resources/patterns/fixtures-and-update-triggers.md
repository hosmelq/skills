# Resource Fixtures And Update Triggers

## When To Use

Use this leaf for resource fixture design and update triggers.

## Pattern

### Fixture Rules

- Set explicit values instead of relying on faker for serialized fields.
- Use coherent related models when the resource reads nested relationships.
- Use explicit synthetic person names unless the contract needs another value.


### Update Triggers

Update this path whenever a resource adds, removes, renames, reorders, reformats, conditionally hides, or nests fields.

## Related References

- [Parent router](../README.md)
