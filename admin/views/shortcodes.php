<?php
/**
 * Shortcodes reference page.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}
?>
<div class="wrap bhg-wrap">
        <h1><?php echo esc_html( bhg_t( 'bhg_shortcode_reference', 'Shortcode Reference' ) ); ?></h1>
        <p class="description">
                <?php
                echo esc_html( bhg_t( 'bhg_shortcode_reference_intro', 'Use the following options to tailor the Bonus Hunt Guesser shortcodes to your needs.' ) );
                ?>
        </p>

        <h2><?php echo esc_html( bhg_t( 'bhg_prizes_shortcode', '[bhg_prizes]' ) ); ?></h2>
        <p>
                <?php
                echo esc_html( bhg_t( 'bhg_prizes_shortcode_desc', 'Outputs a list of available prizes using the same layouts as the active hunt display.' ) );
                ?>
        </p>

        <table class="widefat striped" style="max-width:720px;">
                <thead>
                        <tr>
                                <th scope="col"><?php echo esc_html( bhg_t( 'label_attribute', 'Attribute' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'label_description', 'Description' ) ); ?></th>
                                <th scope="col"><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th>
                        </tr>
                </thead>
                <tbody>
                        <tr>
                                <td><code>category</code></td>
                                <td>
                                        <?php
                                        echo esc_html( bhg_t( 'bhg_prizes_shortcode_category', 'Filter results to a specific prize category (cash_money, casino_money, coupons, merchandise, various).' ) );
                                        ?>
                                </td>
                                <td><code>-</code></td>
                        </tr>
                        <tr>
                                <td><code>design</code></td>
                                <td>
                                        <?php
                                        echo esc_html( bhg_t( 'bhg_prizes_shortcode_design', 'Choose the layout. Use "grid" for a responsive grid or "carousel" for the slider layout.' ) );
                                        ?>
                                </td>
                                <td><code>grid</code></td>
                        </tr>
                        <tr>
                                <td><code>size</code></td>
                                <td>
                                        <?php
                                        echo esc_html( bhg_t( 'bhg_prizes_shortcode_size', 'Select which image size to load for prize cards: small, medium, or big.' ) );
                                        ?>
                                </td>
                                <td><code>medium</code></td>
                        </tr>
                        <tr>
                                <td><code>active</code></td>
                                <td>
                                        <?php
                                        echo esc_html( bhg_t( 'bhg_prizes_shortcode_active', 'Control whether only active prizes are shown. Accepts yes/no or 1/0.' ) );
                                        ?>
                                </td>
                                <td><code>yes</code></td>
                        </tr>
                </tbody>
        </table>

        <p>
                <?php
                echo wp_kses_post(
                        sprintf(
                                bhg_t( 'bhg_prizes_shortcode_example', 'Example: %s' ),
                                '<code>[bhg_prizes design="carousel" size="big" active="yes"]</code>'
                        )
                );
                ?>
        </p>
</div>
