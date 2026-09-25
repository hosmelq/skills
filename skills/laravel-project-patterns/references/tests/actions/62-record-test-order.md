# Action Tests: Parent and Child Lifecycle Order

Ordered integration action cases for stateful records and nested children: state and relation guards, uniqueness, defaults, field persistence, historical selections and transactional deletion. Select only contracts present in the inspected action.

Use each example’s canonical names and case order. These groups complement the [action checklist](00-action-test-order.md); mocked resolver failures, observed lock SQL and two-connection index conflicts establish different evidence.

## Create a parent

1. [Create State Guards](63-create-record-state.md)
2. [Create Owner Guards](64-create-record-owner.md)
3. [Create Role-Specific Relation Guards](65-create-record-facilities.md)
4. [Create Assignment and Rule Guards](66-create-record-selections.md)
5. [Translate a Create Resolver Failure](67-create-record-resolver-failure.md)
6. [Create Related Selection Guards](68-create-record-compatibility.md)
7. [Create Reference Uniqueness](69-create-record-reference.md)
8. [Propagate a Create Database Failure](70-create-record-database-failure.md)
9. [Translate an Insert Index Conflict](71-create-record-index-conflict.md)
10. [Observe Create Query Order](72-create-record-query-order.md)
11. [Create Record Fields](73-create-record-fields.md)
12. [Create Defaults and Explicit Nulls](74-create-record-defaults.md)
13. [Create Explicit Relation Selections](75-create-record-explicit-relations.md)
14. [Create Required Fields](76-create-record-required.md)

## Create a child

1. [Create Child Guards](77-create-nested-record-guards.md)
2. [Create Child Fields](78-create-nested-record-fields.md)

## Update a parent

1. [Update State Guards](79-update-record-state.md)
2. [Update New Relation Guards](80-update-record-selections.md)
3. [Translate an Update Resolver Failure](81-update-record-resolver-failure.md)
4. [Update Related Selection Guards](82-update-record-compatibility.md)
5. [Update Coupled Field Guards](83-update-record-measurements.md)
6. [Update Reference Uniqueness](84-update-record-reference.md)
7. [Propagate an Update Database Failure](85-update-record-database-failure.md)
8. [Translate an Update Index Conflict](86-update-record-index-conflict.md)
9. [Update Record Fields](87-update-record-fields.md)
10. [Preserve Historical Relation Selections](88-update-record-historical-relations.md)
11. [Clear Nullable Record Fields](89-update-record-nullable.md)
12. [Update Coupled Selections](90-update-record-coupled-selection.md)

## Update a child

1. [Update Child State Guards](91-update-nested-record-state.md)
2. [Update Child Group Selection](92-update-nested-record-group.md)
3. [Update Child Fields](93-update-nested-record-fields.md)
4. [Clear Nullable Child Fields](94-update-nested-record-nullable.md)

## Delete a parent

1. [Delete State Guards](95-delete-record-state.md)
2. [Roll Back Child Deletion](96-delete-record-rollback.md)
3. [Delete Active Children Only](97-delete-record-cascade.md)

## Delete a child

1. [Delete a Child Record](98-delete-nested-record.md)
