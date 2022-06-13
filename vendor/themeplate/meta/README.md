# ThemePlate Meta

## Usage

```php
use ThemePlate\Meta\MenuMeta;

( new MenuMeta( 'Custom Menu Meta' ) )->fields( $list )->create();
```

```php
use ThemePlate\Meta\PostMeta;

( new PostMeta( 'Custom Post Meta' ) )->fields( $list )->locaction( 'post_type' )->create();
```

```php
use ThemePlate\Meta\TermMeta;

( new TermMeta( 'Custom Term Meta' ) )->fields( $list )->locaction( 'taxonomy' )->create();
```

```php
use ThemePlate\Meta\UserMeta;

( new UserMeta( 'Custom User Meta' ) )->fields( $list )->create();
```
