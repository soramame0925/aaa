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
$post_id        = isset( $data['post_id'] ) ? (int) $data['post_id'] : get_the_ID();
$circle_terms   = isset( $data['circle_terms'] ) && is_array( $data['circle_terms'] ) ? $data['circle_terms'] : [];
$voice_terms    = isset( $data['voice_actor_terms'] ) && is_array( $data['voice_actor_terms'] ) ? $data['voice_actor_terms'] : [];
$artist_terms   = isset( $data['illustrator_terms'] ) && is_array( $data['illustrator_terms'] ) ? $data['illustrator_terms'] : [];
$genre_terms    = isset( $data['genre_terms'] ) && is_array( $data['genre_terms'] ) ? $data['genre_terms'] : [];

$render_terms = static function ( $terms ) {
    if ( empty( $terms ) ) {
        return '&mdash;';
    }

    $links = [];
    foreach ( $terms as $term ) {
        if ( ! $term instanceof WP_Term ) {
            continue;
        }

        if ( 'uncategorized' === $term->slug ) {
            continue;
        }

        $link = get_term_link( $term );
        if ( ! is_wp_error( $link ) ) {
            $links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
        } else {
            $links[] = esc_html( $term->name );
        }
    }

    return $links ? implode( ' / ', $links ) : '&mdash;';
};

$now_timestamp       = current_time( 'timestamp' );
$sale_end_timestamp  = null;
$sale_price_numeric  = '' !== $sale_price && is_numeric( $sale_price ) ? $sale_price : null;

if ( '' !== $sale_end_date && null !== $sale_end_date ) {
    if ( is_numeric( $sale_end_date ) ) {
        $sale_end_timestamp = (int) $sale_end_date;
    } else {
        $sale_end_timestamp = strtotime( $sale_end_date );
        if ( $sale_end_timestamp ) {
            $sale_end_timestamp = strtotime( gmdate( 'Y-m-d 23:59:59', $sale_end_timestamp ) );
        }
    }
}

$sale_active = null !== $sale_price_numeric && $sale_end_timestamp && $now_timestamp < $sale_end_timestamp;

$sale_end_display = '';
if ( $sale_active && $sale_end_timestamp ) {
    $sale_end_display = date_i18n( 'Y年n月j日', $sale_end_timestamp );
}

$release_date_display = '&mdash;';
if ( $release_date ) {
    $release_date_object = DateTime::createFromFormat( 'Y-m-d', $release_date );

    if ( $release_date_object instanceof DateTime ) {
        $release_date_display = wp_date( 'Y年n月j日', $release_date_object->getTimestamp() );
    } else {
        $release_timestamp = strtotime( $release_date );
        $release_date_display = false !== $release_timestamp ? wp_date( 'Y年n月j日', $release_timestamp ) : $release_date;
    }
}

$release_date_output = '&mdash;' === $release_date_display ? $release_date_display : esc_html( $release_date_display );

$price_markup = '';
if ( $sale_active ) {
    $price_markup = '<div class="mno-pm-price">';
    $price_markup .= '<p class="mno-pm-price__sale"><span class="mno-pm-price__label">' . esc_html__( 'Sale', 'mno-post-manager' ) . '</span>' . esc_html( $sale_price_numeric ) . '</p>';

    if ( $normal_price ) {
        $price_markup .= '<p class="mno-pm-price__normal">' . esc_html( $normal_price ) . '</p>';
    }

    if ( $sale_end_display ) {
        $price_markup .= '<p class="mno-pm-price__end">セール終了 ' . esc_html( $sale_end_display ) . 'まで</p>';
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
        <ul class="mno-pm-list">
            <li><span>サークル名：</span><?php echo $render_terms( $circle_terms ); ?></li>
            <li><span>声優：</span><?php echo $render_terms( $voice_terms ); ?></li>
            <li><span>価格：</span><?php echo $sale_active && $sale_price ? esc_html( $sale_price ) : ( $normal_price ? esc_html( $normal_price ) : '&mdash;' ); ?></li>
            <li><span>イラスト：</span><?php echo $render_terms( $artist_terms ); ?></li>
             <li><span>発売日：</span><?php echo $release_date_output; ?></li>
            <li><span>ジャンル：</span><?php echo $render_terms( $genre_terms ); ?></li>
        </ul>
    </section>

    <section class="mno-pm-article__section">
        <h2>作品のみどころ</h2>
        <?php if ( $highlights ) : ?>
            <ul class="mno-pm-list mno-pm-list--bullets">
                <?php foreach ( $highlights as $highlight ) : ?>
                   <li><?php echo nl2br( wp_kses_post( $highlight ) ); ?></li>
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
                    $duration      = isset( $track['track_duration'] ) ? $track['track_duration'] : '';
                    $count_display = '' !== $count && null !== $count ? (string) $count : '';
                    ?>
                    <div class="mno-pm-track-list__item">
                        <p class="mno-pm-track-list__title">
                            <span class="mno-pm-track-list__label"><?php printf( esc_html__( 'トラック%d', 'mno-post-manager' ), $index + 1 ); ?></span>
                            <span class="mno-pm-track-list__name"><?php echo $track_name ? nl2br( esc_html( $track_name ) ) : '&mdash;'; ?></span>
                             <?php if ( $duration ) : ?>
                                <span class="mno-pm-track-list__duration"><?php echo '(' . esc_html( $duration ) . ')'; ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="mno-pm-track-list__count">
                            <span class="mno-pm-track-list__count-label"><?php esc_html_e( '射精回数', 'mno-post-manager' ); ?></span>
                            <span class="mno-pm-track-list__count-value"><?php echo '' !== $count_display ? esc_html( $count_display ) . esc_html__( '回', 'mno-post-manager' ) : '&mdash;'; ?></span>
                        </p>
                        <p class="mno-pm-track-list__genres">
                            <?php
                            if ( ! empty( $genres ) ) {

                                static $mno_pm_tag_map = null;

                                if ( null === $mno_pm_tag_map ) {
                                    $mno_pm_tag_map = [];
                                    $all_tags = get_tags( [ 'hide_empty' => false ] );

                                    if ( $all_tags && ! is_wp_error( $all_tags ) ) {
                                        foreach ( $all_tags as $tag ) {
                                            $mno_pm_tag_map[ $tag->name ] = $tag;
                                        }
                                    }
                                }

                                $output_genres = [];

                                foreach ( $genres as $genre_item ) {
                                    $parts = preg_split( '/[、,\/｜\|]+/u', $genre_item );

                                    foreach ( $parts as $raw_part ) {
                                        $label = trim( str_replace( '、', '', $raw_part ) );
                                        if ( $label === '' ) continue;

                                        if ( isset( $mno_pm_tag_map[ $label ] ) ) {
                                            $term = $mno_pm_tag_map[ $label ];
                                            $url  = get_tag_link( $term->term_id );
                                            $output_genres[] =
                                               '<a href="' . esc_url( $url ) . '" class="mno-track-genre" style="display:inline-block;">'
                                                . esc_html( $label ) .
                                                '</a>';
                                        } else {
                                            $output_genres[] = '<span class="mno-track-genre" style="display:inline-block;">' . esc_html( $label ) . '</span>';
                                        }
                                    }
                                }

                                echo implode( ' ', $output_genres );
                            } else {
                                echo '&mdash;';
                            }
                            ?>
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