# Contributing

When submitting pull requests please follow the following guidelines:

## QA

There are a few QA tests which every commit has to pass. Please install the following Git hooks:

```bash
# from project root
cp config/pre-commit .git/hooks/
cp config/pre-push .git/hooks/
```

These hooks enforce coding style, code coverage, use of @covers annotation and checks for debug statements. On `pre-commit` only coding style is enforced, on `pre-push` everything has to pass.

## Git Commit Messages

1. Separate subject from body with a blank line
2. Limit the subject line to 50 characters
3. Capitalize the subject line
4. Do not end the subject line with a period
5. Use the imperative mood in the subject line
6. Wrap the body at 72 characters
7. Use the body to explain what and why vs. how

Reference: [How to Write a Git Commit Message by Chris Beams](http://chris.beams.io/posts/git-commit/)

## Coding Style

[PSR-2](http://www.php-fig.org/psr/psr-2/)

## Type Safety

 - PHP is a dynamic language and that is great for many things
 - BUT with great power comes great responsibility
 - so please use type hints where ever possible 
 - functional methods (each, reduce, map, ...) are preferable to loops
