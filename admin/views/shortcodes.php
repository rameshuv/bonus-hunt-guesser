<?php
/**
 * Shortcodes reference for administrators.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
        wp_die(
                esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) )
        );
}

$groups = array();
if ( class_exists( 'BHG_Shortcodes' ) && method_exists( 'BHG_Shortcodes', 'get_documented_shortcodes' ) ) {
        $groups = BHG_Shortcodes::get_documented_shortcodes();
}

?>
<div class="wrap bhg-admin bhg-shortcodes">
        <h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
        <p class="description"><?php echo esc_html( bhg_t( 'shortcode_reference_intro', 'Use this reference to embed Bonus Hunt features on the front-end. Each shortcode mirrors the live implementation so documentation stays in sync with the code.' ) ); ?></p>

        <?php if ( empty( $groups ) ) : ?>
                <div class="notice notice-warning">
                        <p><?php echo esc_html( bhg_t( 'shortcode_reference_unavailable', 'No shortcode documentation is available.' ) ); ?></p>
                </div>
        <?php else : ?>
                <?php foreach ( $groups as $group_key => $group ) :
                        $label = isset( $group['label'] ) ? $group['label'] : ucfirst( (string) $group_key );
                        $desc  = isset( $group['description'] ) ? $group['description'] : '';
                        $items = isset( $group['items'] ) && is_array( $group['items'] ) ? $group['items'] : array();
                        if ( empty( $items ) ) {
                                continue;
                        }
                        ?>
                        <section class="bhg-shortcode-group" aria-label="<?php echo esc_attr( $label ); ?>">
                                <h2><?php echo esc_html( $label ); ?></h2>
                                <?php if ( $desc ) : ?>
                                        <p class="description"><?php echo esc_html( $desc ); ?></p>
                                <?php endif; ?>

                                <?php $first = true; ?>
                                <?php foreach ( $items as $shortcode ) :
                                        $tag         = isset( $shortcode['tag'] ) ? (string) $shortcode['tag'] : '';
                                        $name        = isset( $shortcode['name'] ) ? (string) $shortcode['name'] : $tag;
                                        $description = isset( $shortcode['description'] ) ? (string) $shortcode['description'] : '';
                                        $usage       = isset( $shortcode['usage'] ) ? (string) $shortcode['usage'] : ( $tag ? '[' . $tag . ']' : '' );
                                        $aliases     = isset( $shortcode['aliases'] ) && is_array( $shortcode['aliases'] ) ? array_filter( array_map( 'trim', $shortcode['aliases'] ) ) : array();
                                        $attributes  = isset( $shortcode['attributes'] ) && is_array( $shortcode['attributes'] ) ? $shortcode['attributes'] : array();
                                        $notes       = isset( $shortcode['notes'] ) ? (string) $shortcode['notes'] : '';
                                        ?>
                                        <details class="bhg-shortcode-card" <?php echo $first ? ' open' : ''; ?>>
                                                <summary>
                                                        <code>[<?php echo esc_html( $tag ); ?>]</code>
                                                        <span class="bhg-shortcode-name"><?php echo esc_html( $name ); ?></span>
                                                </summary>
                                                <div class="bhg-shortcode-body">
                                                        <?php if ( $description ) : ?>
                                                                <p><?php echo esc_html( $description ); ?></p>
                                                        <?php endif; ?>

                                                        <?php if ( $usage ) : ?>
                                                                <p><strong><?php echo esc_html( bhg_t( 'label_usage', 'Usage' ) ); ?>:</strong> <code><?php echo esc_html( $usage ); ?></code></p>
                                                        <?php endif; ?>

                                                        <?php if ( ! empty( $aliases ) ) : ?>
                                                                <p><strong><?php echo esc_html( bhg_t( 'label_aliases', 'Aliases' ) ); ?>:</strong>
                                                                        <?php foreach ( $aliases as $alias_index => $alias ) : ?>
                                                                                <code><?php echo esc_html( '[' . $alias . ']' ); ?></code><?php echo $alias_index < count( $aliases ) - 1 ? ', ' : ''; ?>
                                                                        <?php endforeach; ?>
                                                                </p>
                                                        <?php endif; ?>

                                                        <?php if ( ! empty( $attributes ) ) : ?>
                                                                <div class="bhg-shortcode-attributes">
                                                                        <h3><?php echo esc_html( bhg_t( 'label_attributes', 'Attributes' ) ); ?></h3>
                                                                        <table class="widefat fixed striped">
                                                                                <thead>
                                                                                        <tr>
                                                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_attribute', 'Attribute' ) ); ?></th>
                                                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_type', 'Type' ) ); ?></th>
                                                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th>
                                                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_options', 'Options' ) ); ?></th>
                                                                                                <th scope="col"><?php echo esc_html( bhg_t( 'label_description', 'Description' ) ); ?></th>
                                                                                        </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?php foreach ( $attributes as $attr_key => $attribute ) :
                                                                                                $attr_label = isset( $attribute['label'] ) ? (string) $attribute['label'] : ( is_string( $attr_key ) ? (string) $attr_key : '' );
                                                                                                $attr_type  = isset( $attribute['type'] ) ? (string) $attribute['type'] : '';
                                                                                                $default    = isset( $attribute['default'] ) ? $attribute['default'] : '';
                                                                                                $options    = isset( $attribute['options'] ) ? $attribute['options'] : '';
                                                                                                $attr_desc  = isset( $attribute['description'] ) ? (string) $attribute['description'] : '';

                                                                                                if ( is_array( $options ) ) {
                                                                                                        $options = implode( ', ', array_map( 'strval', $options ) );
                                                                                                }

                                                                                                $default_display = is_array( $default ) ? implode( ', ', array_map( 'strval', $default ) ) : ( is_bool( $default ) ? ( $default ? 'true' : 'false' ) : (string) $default );
                                                                                                ?>
                                                                                                <tr>
                                                                                                        <th scope="row"><code><?php echo esc_html( $attr_label ); ?></code></th>
                                                                                                        <td><?php echo esc_html( $attr_type ); ?></td>
                                                                                                        <td><?php echo esc_html( $default_display ); ?></td>
                                                                                                        <td><?php echo esc_html( (string) $options ); ?></td>
                                                                                                        <td><?php echo esc_html( $attr_desc ); ?></td>
                                                                                                </tr>
                                                                                        <?php endforeach; ?>
                                                                                </tbody>
                                                                        </table>
                                                                </div>
                                                        <?php endif; ?>

                                                        <?php if ( $notes ) : ?>
                                                                <p class="description"><?php echo esc_html( $notes ); ?></p>
                                                        <?php endif; ?>
                                                </div>
                                        </details>
                                        <?php $first = false; ?>
                                <?php endforeach; ?>
                        </section>
                <?php endforeach; ?>
        <?php endif; ?>
</div>
