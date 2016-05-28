# Scope

Reference: [PHP documentation on variable scope](http://php.net/manual/en/language.variables.scope.php)

 - superglobals like `$_POST`
 - global scope if variable defined outside of a class or function
 - referenced global through `global` or `$GLOBALS`
 - static variables have normal scope, but don't change after the scope is left
