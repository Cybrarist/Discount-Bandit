# Naming

- Functions and methods you write must be snake_case: `create_links_for_default_product()`, `prepare_base_key_and_params()`, not `createLinksForDefaultProduct()`. This overrides the camelCase examples elsewhere in these guidelines; keep names descriptive.
- Test methods are snake_case with the `test_` prefix: `test_links_are_ordered_by_raw_price()`.
- Exception: methods whose name is dictated by a framework or package (overrides and hooks the framework calls by name, such as `setUp()`, `casts()`, `getDefaultName()`, `configure()`, `handle()`, `__invoke()`, relationship and accessor conventions) keep the name the framework expects.
