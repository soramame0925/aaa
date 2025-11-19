<?php
/** @var array $data */
$gallery        = ! empty( $data['gallery'] ) ? $data['gallery'] : [];
$voice_sample   = isset( $data['voice_sample'] ) ? $data['voice_sample'] : '';
$circle_name    = isset( $data['circle_name'] ) ? $data['circle_name'] : '';
$voice_actors   = ! empty( $data['voice_actors'] ) ? $data['voice_actors'] : [];
$illustrators   = ! empty( $data['illustrators'] ) ? $data['illustrators'] : [];
$normal_price   = isset( $data['normal_price'] ) ? $data['normal_price'] : '';
$sale_price     = isset( $data['sale_price'] ) ? $data['sale_price'] : '';
$sale_end_date  = isset( $data['sale_end_date'] ) ? $data['sale_end_date'] : '';
$highlights     = ! empty( $data['highlights'] ) ? $data['highlights'] : [];
$track_list     = ! empty( $data['track_list'] ) ? $data['track_list'] : [];
$quote_blocks   = ! empty( $data['quote_blocks'] ) ? $data['quote_blocks'] : [];
$release_date   = isset( $data['release_date'] ) ? $data['release_date'] : '';
$genre          = isset( $data['genre'] ) ? $data['genre'] : '';
$track_duration = isset( $data['track_duration'] ) ? $data['track_duration'] : '';
$buy_url        = isset( $data['buy_url'] ) ? $data['buy_url'] : '';
$data_bars      = ! empty( $data['data_bars'] ) ? $data['data_bars'] : [];
$data_voice     = ! empty( $data['data_voice'] ) ? $data['data_voice'] : [];
$data_level     = isset( $data['data_level'] ) ? $data['data_level'] : '';
$voice_types    = MNO_Post_Manager::get_voice_type_options();
$level_labels   = MNO_Post_Manager::get_level_options();

$genre_fallback_items = [];
if ( is_array( $genre ) ) {
    $genre_fallback_items = array_filter( array_map( 'trim', $genre ), 'strlen' );
} elseif ( is_string( $genre ) && '' !== $genre ) {
    $genre_fallback_items = array_filter( array_map( 'trim', preg_split( "/[,、\n\/]+/u", $genre ) ), 'strlen' );
}

if ( ! function_exists( 'mno_pm_get_taxonomy_links' ) ) {
    /**
     * Retrieve linked taxonomy markup for the first taxonomy that has terms.
     *
     * @param int   $post_id     Post ID.
     * @param array $taxonomies  Candidate taxonomies in priority order.
     * @param string $link_class Class attribute for each link.
     *
     * @return string HTML markup for the links or empty string.
     */
    function mno_pm_get_taxonomy_links( $post_id, array $taxonomies, $link_class = 'mno-link-tag' ) {
        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_the_terms( $post_id, $taxonomy );
            if ( empty( $terms ) || is_wp_error( $terms ) ) {
                continue;
            }

            $links = [];
            foreach ( $terms as $term ) {
                $term_link = get_term_link( $term );
                if ( is_wp_error( $term_link ) ) {
                    continue;
                }

                $links[] = sprintf(
                    '<a class="%s" href="%s">%s</a>',
                    esc_attr( $link_class ),
                    esc_url( $term_link ),
                    esc_html( $term->name )
                );
            }

            if ( $links ) {
                return implode( '', $links );
            }
        }

        return '';
    }
}

$post_id = get_the_ID();

$circle_taxonomies      = [ 'circle', 'circle-name', 'circle_name', 'mno_circle' ];
$voice_actor_taxonomies = [ 'voice_actor', 'voice-actor', 'voice_actors', 'mno_voice_actor' ];
$illustrator_taxonomies = [ 'illustrator', 'illustrators', 'artist', 'mno_illustrator' ];
$genre_taxonomies       = [ 'genre', 'genres', 'mno_genre' ];

$circle_links_markup      = mno_pm_get_taxonomy_links( $post_id, $circle_taxonomies );
$voice_links_markup       = mno_pm_get_taxonomy_links( $post_id, $voice_actor_taxonomies );
$illustrator_links_markup = mno_pm_get_taxonomy_links( $post_id, $illustrator_taxonomies );
$genre_links_markup       = mno_pm_get_taxonomy_links( $post_id, $genre_taxonomies );

$now_timestamp = current_time( 'timestamp' );
$sale_active   = $sale_price && ( ! $sale_end_date || ( $sale_end_date && strtotime( $sale_end_date . ' 23:59:59' ) >= $now_timestamp ) );

$sale_end_display = '';
if ( $sale_end_date ) {
    $sale_end_timestamp = strtotime( $sale_end_date );
    $sale_end_display   = $sale_end_timestamp ? date_i18n( 'Y年n月j日', $sale_end_timestamp ) : $sale_end_date;
}

$price_markup = '';
if ( $sale_price || $normal_price || $sale_end_display ) {
    $price_markup .= '<div class="mno-pm-price">';
    if ( $sale_active && $sale_price ) {
        $price_markup .= '<p class="mno-pm-price__sale"><span class="mno-pm-price__label">' . esc_html__( 'Sale', 'mno-post-manager' ) . '</span>' . esc_html( $sale_price ) . '</p>';
        if ( $normal_price ) {
            $price_markup .= '<p class="mno-pm-price__normal">' . esc_html( $normal_price ) . '</p>';
        }
    } elseif ( $normal_price ) {
        $price_markup .= '<p class="mno-pm-price__normal mno-pm-price__normal--only">' . esc_html( $normal_price ) . '</p>';
    }

    if ( $sale_end_display ) {
        $price_markup .= '<p class="mno-pm-price__end">セール終了：' . esc_html( $sale_end_display ) . 'まで</p>';
    }

    $price_markup .= '</div>';
}

$voice_sample_markup = '';
if ( $voice_sample ) {
    if ( filter_var( $voice_sample, FILTER_VALIDATE_URL ) ) {
        $embed = wp_oembed_get( $voice_sample );
        if ( ! $embed && preg_match( '/\.mp3$|\.wav$|\.m4a$/i', $voice_sample ) ) {
            $embed = '<audio controls preload="none" class="mno-pm-voice-sample__audio"><source src="' . esc_url( $voice_sample ) . '" /></audio>';
        }
        if ( ! $embed && strpos( $voice_sample, 'chobit' ) !== false ) {
            $embed = '<iframe class="mno-pm-voice-sample__iframe" src="' . esc_url( $voice_sample ) . '" loading="lazy" allow="autoplay"></iframe>';
        }
        if ( ! $embed ) {
            $embed = '<a class="mno-pm-voice-sample__link" href="' . esc_url( $voice_sample ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open voice sample', 'mno-post-manager' ) . '</a>';
        }
        $voice_sample_markup = $embed;
    } else {
        $voice_sample_markup = wp_kses( $voice_sample, MNO_Post_Manager::get_voice_sample_allowed_tags() );
    }
}

$buy_button = '';
$button_label = esc_html__( 'DLsiteで購入', 'mno-post-manager' );
if ( $buy_url ) {
    $buy_button = '<a class="mno-pm-buy-button" href="' . esc_url( $buy_url ) . '" target="_blank" rel="noopener noreferrer">' . $button_label . '</a>';
} else {
    $buy_button = '<span class="mno-pm-buy-button mno-pm-buy-button--disabled" aria-disabled="true">' . $button_label . '</span>';
}
?>
<div class="mno-pm-article">
    <?php if ( $gallery ) : ?>
        <section class="mno-pm-article__section mno-pm-article__gallery" aria-label="<?php esc_attr_e( 'Gallery', 'mno-post-manager' ); ?>">
            <div class="mno-pm-slider mno-gallery" data-mno-pm-slider data-mno-gallery-slider>
                <div class="mno-pm-slider__track mno-gallery-track">
                    <?php foreach ( $gallery as $image_id ) :
                        $image_html = wp_get_attachment_image( $image_id, 'large', false, [ 'class' => 'mno-pm-slider__image' ] );
                        if ( ! $image_html ) {
                            continue;
                        }
                        ?>
                        <figure class="mno-pm-slider__slide mno-gallery-slide">
                            <?php echo $image_html; ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <button
                    type="button"
                    class="mno-pm-slider__nav mno-gallery-arrow mno-gallery-arrow--left mno-pm-slider__nav--prev"
                    aria-label="<?php esc_attr_e( 'Previous', 'mno-post-manager' ); ?>"
                >&#10094;</button>
                <button
                    type="button"
                    class="mno-pm-slider__nav mno-gallery-arrow mno-gallery-arrow--right mno-pm-slider__nav--next"
                    aria-label="<?php esc_attr_e( 'Next', 'mno-post-manager' ); ?>"
                >&#10095;</button>
                <div class="mno-pm-slider__dots mno-gallery-dots" role="tablist" aria-label="<?php esc_attr_e( 'Gallery navigation', 'mno-post-manager' ); ?>"></div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( $voice_sample_markup ) : ?>
        <section class="mno-pm-article__section mno-pm-article__voice">
            <div class="mno-voice-sample">
                <?php echo $voice_sample_markup; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( $price_markup || $buy_button ) : ?>
        <section class="mno-pm-article__section mno-pm-article__purchase">
            <?php echo $price_markup; ?>
            <?php echo $buy_button; ?>
        </section>
    <?php endif; ?>

    <section class="mno-pm-article__section">
        <h2>サークル情報</h2>
         <ul class="mno-pm-list mno-circle-info-list">
            <li class="mno-item">
                <span class="mno-item-label">サークル名：</span>
                <span class="mno-item-value">
                    <?php if ( $circle_links_markup ) : ?>
                        <?php echo $circle_links_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php elseif ( $circle_name ) : ?>
                        <span><?php echo esc_html( $circle_name ); ?></span>
                    <?php else : ?>
                        <span>&mdash;</span>
                    <?php endif; ?>
                </span>
            </li>
            <li class="mno-item">
                <span class="mno-item-label">声優：</span>
                <span class="mno-item-value">
                    <?php if ( $voice_links_markup ) : ?>
                        <?php echo $voice_links_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php elseif ( $voice_actors ) : ?>
                        <span><?php echo esc_html( implode( ' / ', $voice_actors ) ); ?></span>
                    <?php else : ?>
                        <span>&mdash;</span>
                    <?php endif; ?>
                </span>
            </li>
            <li class="mno-item">
                <span class="mno-item-label">価格：</span>
                <span class="mno-item-value">
                    <?php
                    if ( $sale_active && $sale_price ) {
                        echo esc_html( $sale_price );
                    } elseif ( $normal_price ) {
                        echo esc_html( $normal_price );
                    } else {
                        echo '&mdash;';
                    }
                    ?>
                </span>
            </li>
            <li class="mno-item">
                <span class="mno-item-label">イラスト：</span>
                <span class="mno-item-value">
                    <?php if ( $illustrator_links_markup ) : ?>
                        <?php echo $illustrator_links_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php elseif ( $illustrators ) : ?>
                        <span><?php echo esc_html( implode( ' / ', $illustrators ) ); ?></span>
                    <?php else : ?>
                        <span>&mdash;</span>
                    <?php endif; ?>
                </span>
            </li>
            <li class="mno-item">
                <span class="mno-item-label">発売日：</span>
                <span class="mno-item-value">
                    <?php echo $release_date ? esc_html( $release_date ) : '&mdash;'; ?>
                </span>
            </li>
            <li class="mno-item">
                <span class="mno-item-label">ジャンル：</span>
                <span class="mno-item-value">
                    <?php if ( $genre_links_markup ) : ?>
                        <div class="mno-genre-wrap">
                            <?php echo $genre_links_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php elseif ( ! empty( $genre_fallback_items ) ) : ?>
                        <div class="mno-genre-wrap">
                            <?php foreach ( $genre_fallback_items as $genre_item ) : ?>
                                <span class="mno-genre-fallback-item"><?php echo esc_html( $genre_item ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <span>&mdash;</span>
                    <?php endif; ?>
                </span>
            </li>
        </ul>
    </section>

    <section class="mno-pm-article__section">
        <h2>作品のみどころ</h2>
        <?php if ( $highlights ) : ?>
            <ul class="mno-pm-list mno-pm-list--bullets">
                <?php foreach ( $highlights as $highlight ) : ?>
                    <li><?php echo esc_html( $highlight ); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>&mdash;</p>
        <?php endif; ?>
    </section>

    <?php if ( $data_bars || $data_voice || $data_level ) : ?>
        <section class="mno-pm-article__section mno-data-section">
            <h2>データ</h2>

            <?php if ( $data_bars ) :
                $max_count = 0;
                foreach ( $data_bars as $entry ) {
                    $entry_count = isset( $entry['count'] ) && '' !== $entry['count'] ? (int) $entry['count'] : 0;
                    if ( $entry_count > $max_count ) {
                        $max_count = $entry_count;
                    }
                }
                ?>
                <div class="mno-data-block mno-data-block--bars">
                    <h3><?php esc_html_e( '演出データ', 'mno-post-manager' ); ?></h3>
                    <div class="mno-data-bar-chart" role="list">
                        <?php foreach ( $data_bars as $entry ) :
                            $label        = isset( $entry['label'] ) ? $entry['label'] : '';
                            $track        = isset( $entry['track'] ) ? $entry['track'] : '';
                            $count_raw    = isset( $entry['count'] ) ? $entry['count'] : '';
                            $count_value  = '' !== $count_raw ? (int) $count_raw : 0;
                            $percent      = $max_count ? min( 100, round( ( $count_value / $max_count ) * 100, 2 ) ) : 0;
                            $count_output = '' !== $count_raw ? (string) $count_value : '';
                            ?>
                            <div class="mno-data-bar" role="listitem">
                                <div class="mno-data-label">
                                    <span class="mno-data-label-text"><?php echo $label ? esc_html( $label ) : '&mdash;'; ?></span>
                                    <?php if ( $track ) : ?>
                                        <span class="mno-data-track"><?php echo esc_html( $track ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mno-data-bar-track" aria-hidden="true">
                                    <span class="mno-data-bar-fill" style="--mno-bar-width: <?php echo esc_attr( $percent ); ?>%;"></span>
                                </div>
                                <span class="mno-data-count"><?php echo '' !== $count_output ? esc_html( $count_output ) . esc_html__( '回', 'mno-post-manager' ) : '&mdash;'; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $data_voice ) : ?>
                <div class="mno-data-block mno-data-block--voice">
                    <h3><?php esc_html_e( '声の高さ', 'mno-post-manager' ); ?></h3>
                    <div class="mno-voice-chart" role="table" style="--mno-voice-count: <?php echo esc_attr( count( $voice_types ) ); ?>;">
                        <div class="mno-voice-row mno-voice-row--header" role="row">
                            <span class="mno-voice-name" role="columnheader"><?php esc_html_e( '声優名', 'mno-post-manager' ); ?></span>
                            <?php foreach ( $voice_types as $type_label ) : ?>
                                <span class="mno-voice-type" role="columnheader"><?php echo esc_html( $type_label ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php foreach ( $data_voice as $entry ) :
                            $name           = isset( $entry['name'] ) ? $entry['name'] : '';
                            $types          = isset( $entry['types'] ) && is_array( $entry['types'] ) ? $entry['types'] : [];
                            $active_labels  = array_values(
                                array_filter(
                                    array_map(
                                        function ( $type_key ) use ( $voice_types ) {
                                            return isset( $voice_types[ $type_key ] ) ? $voice_types[ $type_key ] : '';
                                        },
                                        $types
                                    ),
                                    function ( $label ) {
                                        return '' !== $label;
                                    }
                                )
                            );
                            $active_labels  = array_map( 'trim', $active_labels );
                            $active_labels  = array_filter( $active_labels, 'strlen' );
                            if ( $active_labels ) {
                                $summary_items = array_map(
                                    function ( $label ) {
                                        return sprintf(
                                            '<span class="mno-voice-mobile__type-item">%s</span>',
                                            esc_html( $label )
                                        );
                                    },
                                    $active_labels
                                );
                                $summary_output = implode( '', $summary_items );
                            } else {
                                $summary_output = sprintf(
                                    '<span class="mno-voice-mobile__type-item">%s</span>',
                                    esc_html__( '未選択', 'mno-post-manager' )
                                );
                            }
                            ?>
                            <div class="mno-voice-row" role="row">
                                <span class="mno-voice-name" role="rowheader"><?php echo $name ? esc_html( $name ) : '&mdash;'; ?></span>
                                <?php foreach ( $voice_types as $type_key => $type_label ) :
                                    $is_active = in_array( $type_key, $types, true );
                                    ?>
                                    <span class="mno-voice-cell" role="cell">
                                        <span class="mno-voice-dot<?php echo $is_active ? ' is-active' : ''; ?>" aria-hidden="true"></span>
                                        <span class="screen-reader-text"><?php echo esc_html( ( $name ? $name : __( '不明', 'mno-post-manager' ) ) . ' - ' . $type_label . ( $is_active ? __( '：該当', 'mno-post-manager' ) : __( '：非該当', 'mno-post-manager' ) ) ); ?></span>
                                    </span>
                                <?php endforeach; ?>
                                <details class="mno-voice-mobile">
                                    <summary>
                                        <span class="mno-voice-mobile__name"><?php echo $name ? esc_html( $name ) : esc_html__( '不明', 'mno-post-manager' ); ?></span>
                                        <span class="mno-voice-mobile__types"><?php echo wp_kses( $summary_output, [ 'span' => [ 'class' => [] ] ] ); ?></span>
                                    </summary>
                                    <div class="mno-voice-mobile__content">
                                        <ul class="mno-voice-mobile__list" role="list">
                                            <?php foreach ( $voice_types as $type_key => $type_label ) :
                                                $is_active = in_array( $type_key, $types, true );
                                                ?>
                                                <li class="mno-voice-mobile__item">
                                                    <span class="mno-voice-mobile__dot mno-voice-dot<?php echo $is_active ? ' is-active' : ''; ?>" aria-hidden="true"></span>
                                                    <span class="mno-voice-mobile__label"><?php echo esc_html( $type_label ); ?></span>
                                                    <span class="screen-reader-text"><?php echo esc_html( ( $name ? $name : __( '不明', 'mno-post-manager' ) ) . ' - ' . $type_label . ( $is_active ? __( '：該当', 'mno-post-manager' ) : __( '：非該当', 'mno-post-manager' ) ) ); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </details>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( $data_level && isset( $level_labels[ $data_level ] ) ) : ?>
                <div class="mno-data-block mno-data-block--level">
                    <h3><?php esc_html_e( 'Mレベル', 'mno-post-manager' ); ?></h3>
                    <div class="mno-level-chart" role="group" aria-label="<?php esc_attr_e( 'Mレベル', 'mno-post-manager' ); ?>">
                        <?php foreach ( $level_labels as $level_key => $label ) :
                            $is_active = $level_key === $data_level;
                            ?>
                            <div class="mno-level-step">
                                <span class="mno-level-dot<?php echo $is_active ? ' is-active' : ''; ?>" aria-hidden="true"></span>
                                <span class="mno-level-label"><?php echo esc_html( $label ); ?></span>
                                <?php if ( $is_active ) : ?>
                                    <span class="screen-reader-text"><?php echo esc_html__( '選択中', 'mno-post-manager' ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="mno-pm-article__section">
        <h2>トラックリスト</h2>
        <?php if ( $track_list ) : ?>
            <div class="mno-pm-track-list">
                <?php foreach ( $track_list as $index => $track ) :
                    $track_name    = isset( $track['track_name'] ) ? $track['track_name'] : '';
                    $count         = isset( $track['ejaculation_count'] ) ? $track['ejaculation_count'] : '';
                    $genres        = isset( $track['genres'] ) && is_array( $track['genres'] ) ? $track['genres'] : [];
                    $count_display = '' !== $count && null !== $count ? (string) $count : '';
                    ?>
                    <div class="mno-pm-track-list__item">
                        <p class="mno-pm-track-list__title">
                            <span class="mno-pm-track-list__label"><?php printf( esc_html__( 'トラック%d', 'mno-post-manager' ), $index + 1 ); ?></span>
                            <span class="mno-pm-track-list__name"><?php echo $track_name ? esc_html( $track_name ) : '&mdash;'; ?></span>
                        </p>
                        <p class="mno-pm-track-list__count">
                            <span class="mno-pm-track-list__count-label"><?php esc_html_e( '射精回数', 'mno-post-manager' ); ?></span>
                            <span class="mno-pm-track-list__count-value"><?php echo '' !== $count_display ? esc_html( $count_display ) . esc_html__( '回', 'mno-post-manager' ) : '&mdash;'; ?></span>
                        </p>
                        <p class="mno-pm-track-list__genres">
                            <?php echo ! empty( $genres ) ? esc_html( implode( '、', $genres ) ) : '&mdash;'; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p>&mdash;</p>
        <?php endif; ?>
    </section>

    <?php if ( $quote_blocks ) : ?>
        <section class="mno-pm-article__section mno-pm-article__quotes">
            <div class="mno-quote-blocks">
                <?php foreach ( $quote_blocks as $block ) :
                    $heading      = isset( $block['heading'] ) ? $block['heading'] : '';
                    $free_field_1 = isset( $block['free_field_1'] ) ? $block['free_field_1'] : '';
                    $free_field_2 = isset( $block['free_field_2'] ) ? $block['free_field_2'] : '';
                    $quote        = isset( $block['quote'] ) ? $block['quote'] : '';
                    ?>
                    <section class="mno-quote-block">
                        <?php if ( $heading ) : ?>
                            <h3><?php echo esc_html( $heading ); ?></h3>
                        <?php endif; ?>

                        <?php if ( $free_field_1 ) : ?>
                            <p class="mno-quote-block__meta"><?php echo esc_html( $free_field_1 ); ?></p>
                        <?php endif; ?>

                        <?php if ( $free_field_2 ) : ?>
                            <p class="mno-quote-block__meta"><?php echo esc_html( $free_field_2 ); ?></p>
                        <?php endif; ?>

                        <?php if ( $quote ) : ?>
                            <blockquote><?php echo wpautop( esc_html( $quote ) ); ?></blockquote>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="mno-pm-article__section">
        <h2>まとめ</h2>
        <ul class="mno-pm-list">
            <li><span>トラック時間：</span><?php echo $track_duration ? esc_html( $track_duration ) : '&mdash;'; ?></li>
            <li><span>声優：</span><?php echo $voice_actors ? esc_html( implode( ' / ', $voice_actors ) ) : '&mdash;'; ?></li>
            <li><span>ジャンル：</span><?php echo $genre ? esc_html( $genre ) : '&mdash;'; ?></li>
            <li><span>サークル名：</span><?php echo $circle_name ? esc_html( $circle_name ) : '&mdash;'; ?></li>
        </ul>
        <?php echo $buy_button; ?>
    </section>
</div>