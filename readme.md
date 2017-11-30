# WordPress Post Status

A library for better integration of custom post statuses with the UI in WordPress.

## Getting Started

For the most part, use the same arguments as you would when directly calling `register_post_status()` in WordPress:

```php
<?php 

add_action( 'init', function () {

	\wpscholar\WordPress\PostStatus::register( 'archive', [
		'label'                     => esc_html_x( 'Archive', 'post status', 'text-domain' ),
		'labels'                    => [
			'post_state'    => esc_html_x( 'Archived', 'post status state', 'text-domain' ),
		],
		'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'text-domain' ),
		'post_types'                => [ 'post' ],
		'public'                    => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
	] );

} );
```

Just be sure to replace `text-domain` with your actual plugin (or theme) text domain.

### Custom Arguments

The following are custom arguments:

- `labels` - The `post_state` label is used when displaying the status next to the title on the post listing page.
- `post_types` - Unless one or more post types are defined, the new status won't be assignable via the UI.

It is possible to assign any arguments you want when registering a custom post status. You can then filter by those arguments when calling `get_post_stati()`.

### Fetching Custom Labels

A custom label can be fetched as follows:

```php
<?php

// Label returned will be 'Archived' (given registration code from previous example)
$label = \wpscholar\WordPress\PostStatus::getLabel('archive', 'post_state');
```

If the requested label doesn't exist, the fallback is the main `label` property from the post status object.