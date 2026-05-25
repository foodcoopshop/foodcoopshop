# FoodCoopShop - GitHub Copilot Instructions

## When Suggesting Code
- Run all commands inside Docker container `fcs.php`
- Respect CakePHP conventions
- Use PHP 8.4 features appropriately
- Include type hints
- Follow existing code style in the file
- Verify against PHPStan rules (call `composer phpstan`)
- Use trailing commas in arrays, function calls and function definitions where applicable
- Ensure tests pass (call `composer test`)
- Always update existing tests or write new tests for the new implemented features
- If CSS or JS files are modified, there is no need to call `asset_compress build` as this is done automatically on deployment
- New translation keys (msgid) should not be separated with an underscore, use natural language with spaces instead. Skip translations for other languages than German.
- Translation keys starting with "Configuration_" can hold underscores, as they are used in the code as configuration keys. Also translate these keys for all languages.
- Never run `asset_compress build` locally
- Avoid code duplication. If you find yourself copying and pasting code, consider refactoring to create a reusable function or component.