<?php
/**
 * Template part: Site Header / Primary Navigation
 *
 * Reuses existing ACF option fields:
 *   - main_logo            (URL)
 *   - header_logo_alt      (text)
 *   - phone_text           (display)
 *   - phone_link           (digits, prepend tel:)
 *   - email_address        (display + mailto:)
 *
 * Uses new ACF option fields:
 *   - header_cta_label
 *   - header_cta_link      (array: url / title / target)
 *   - nav_items            (repeater — see header_navigation field group)
 */

// ----------------------------------------------------------------------
// Helpers
// ----------------------------------------------------------------------

if ( ! function_exists( 'lumisol_is_nav_link_active' ) ) {
	/**
	 * Lightweight active-state check for nav links.
	 * Matches exact URL, and treats child URLs as active for parent links
	 * (except the homepage, which only matches itself).
	 */
	function lumisol_is_nav_link_active( $url ) {
    if ( empty( $url ) ) {
        return false;
    }

    $home_path = '/' . trim( wp_parse_url( home_url(), PHP_URL_PATH ) ?: '', '/' );

    $current = strtok( home_url( add_query_arg( null, null ) ), '?' );
    $current = '/' . trim( wp_parse_url( $current, PHP_URL_PATH ) ?: '', '/' );
    $target  = '/' . trim( wp_parse_url( $url, PHP_URL_PATH ) ?: '', '/' );

    if ( $target === $home_path ) {
        return $current === $home_path;
    }
    return $current === $target || 0 === strpos( $current, $target . '/' );
}
}

// ----------------------------------------------------------------------
// Data
// ----------------------------------------------------------------------

$logo          = get_field( 'main_logo', 'option' );
$logo_alt      = get_field( 'header_logo_alt', 'option' ) ?: get_bloginfo( 'name' );
$phone_text    = get_field( 'phone_text', 'option' );
$phone_link    = get_field( 'phone_link', 'option' );
$email_address = get_field( 'email_address', 'option' );

$cta_label = get_field( 'header_cta_label', 'option' );
$cta_link  = get_field( 'header_cta_link', 'option' );
?>

<header class="site-header" id="siteHeader">

	<?php /* ------------------------------ Top contact bar */ ?>
	<div class="topbar">
		<div class="container">
			<div class="topbar__inner">

				<?php if ( $email_address ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $email_address ) ); ?>" aria-label="Email Lumisol">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						<?php echo esc_html( antispambot( $email_address ) ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $phone_text && $phone_link ) : ?>
					<a href="tel:<?php echo esc_attr( $phone_link ); ?>" aria-label="Phone Lumisol">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<?php echo esc_html( $phone_text ); ?>
					</a>
				<?php endif; ?>

			</div>
		</div>
	</div>

	<?php /* ------------------------------ Main nav */ ?>
	<div class="container">
		<div class="site-header__inner">

			<a href="<?php echo esc_url( home_url() ); ?>" class="site-header__logo" aria-label="<?php echo esc_attr( $logo_alt ); ?>">
				<?php if ( $logo ) : ?>
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>">
				<?php endif; ?>
			</a>

			<nav class="nav" aria-label="Primary">
				<ul class="nav__list" id="navList">

					<?php if ( have_rows( 'nav_items', 'option' ) ) : ?>
						<?php while ( have_rows( 'nav_items', 'option' ) ) : the_row(); ?>

							<?php
							$label    = get_sub_field( 'label' );
							$nav_type = get_sub_field( 'nav_type' );
							?>

							<?php /* ------------------------------ SIMPLE LINK */ ?>
							<?php if ( 'link' === $nav_type ) :
								$link = get_sub_field( 'link' );
								if ( ! $link ) {
									continue;
								}
								$is_active = lumisol_is_nav_link_active( $link['url'] );
								?>
								<li>
									<a href="<?php echo esc_url( $link['url'] ); ?>"
									   <?php echo ! empty( $link['target'] ) ? 'target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>
									   class="<?php echo $is_active ? 'is-active' : ''; ?>">
										<?php echo esc_html( $label ?: $link['title'] ); ?>
									</a>
								</li>

							<?php /* ------------------------------ DROPDOWN */ ?>
							<?php elseif ( 'dropdown' === $nav_type ) : ?>
								<li class="nav__item nav__item--dropdown">
									<button class="nav__trigger" type="button" aria-haspopup="true" aria-expanded="false">
										<?php echo esc_html( $label ); ?>
										<svg class="nav__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
									</button>
									<?php if ( have_rows( 'dropdown_items' ) ) : ?>
										<ul class="nav__dropdown" role="menu" aria-label="<?php echo esc_attr( $label ); ?>">
											<?php while ( have_rows( 'dropdown_items' ) ) : the_row();
												$d_label = get_sub_field( 'label' );
												$d_link  = get_sub_field( 'link' );
												if ( ! $d_link ) {
													continue;
												}
												?>
												<li>
													<a href="<?php echo esc_url( $d_link['url'] ); ?>"
													   <?php echo ! empty( $d_link['target'] ) ? 'target="' . esc_attr( $d_link['target'] ) . '" rel="noopener"' : ''; ?>
													   role="menuitem">
														<?php echo esc_html( $d_label ?: $d_link['title'] ); ?>
													</a>
												</li>
											<?php endwhile; ?>
										</ul>
									<?php endif; ?>
								</li>

							<?php /* ------------------------------ MEGA MENU */ ?>
							<?php elseif ( 'mega' === $nav_type ) : ?>
								<li class="nav__item nav__item--mega">
									<button class="nav__trigger" type="button" aria-haspopup="true" aria-expanded="false">
										<?php echo esc_html( $label ); ?>
										<svg class="nav__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
									</button>
									<div class="nav__megamenu" role="menu" aria-label="<?php echo esc_attr( $label ); ?>">
										<div class="container">
											<div class="megamenu__grid">

												<?php if ( have_rows( 'mega_columns' ) ) : ?>
													<?php while ( have_rows( 'mega_columns' ) ) : the_row();
														$col_heading = get_sub_field( 'heading' );
														?>
														<div class="megamenu__col">
															<?php if ( $col_heading ) : ?>
																<h4 class="megamenu__heading"><?php echo esc_html( $col_heading ); ?></h4>
															<?php endif; ?>

															<?php if ( have_rows( 'items' ) ) : ?>
																<ul class="megamenu__list">
																	<?php while ( have_rows( 'items' ) ) : the_row();
																		$icon     = get_sub_field( 'icon' );
																		$title    = get_sub_field( 'title' );
																		$subtitle = get_sub_field( 'subtitle' );
																		$link     = get_sub_field( 'link' );
																		if ( ! $link ) {
																			continue;
																		}
																		?>
																		<li>
																			<a href="<?php echo esc_url( $link['url'] ); ?>"
																			   <?php echo ! empty( $link['target'] ) ? 'target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>
																			   role="menuitem">
																				<?php if ( $icon ) : ?>
																					<span class="megamenu__icon"><img src="<?php echo esc_url( $icon ); ?>" alt=""></span>
																				<?php endif; ?>
																				<span class="megamenu__copy">
																					<span class="megamenu__title"><?php echo esc_html( $title ); ?></span>
																					<?php if ( $subtitle ) : ?>
																						<span class="megamenu__sub"><?php echo esc_html( $subtitle ); ?></span>
																					<?php endif; ?>
																				</span>
																			</a>
																		</li>
																	<?php endwhile; ?>
																</ul>
															<?php endif; ?>
														</div>
													<?php endwhile; ?>
												<?php endif; ?>

												<?php /* Mega feature card */ ?>
												<?php
												$feature = get_sub_field( 'mega_feature' );
												if ( $feature && ! empty( $feature['show_feature'] ) ) :
													?>
													<div class="megamenu__feature">
														<?php if ( ! empty( $feature['image'] ) ) : ?>
															<div class="megamenu__feature-img" style="background-image:url('<?php echo esc_url( $feature['image'] ); ?>')"></div>
														<?php endif; ?>
														<div class="megamenu__feature-body">
															<?php if ( ! empty( $feature['tag'] ) ) : ?>
																<span class="megamenu__feature-tag"><?php echo esc_html( $feature['tag'] ); ?></span>
															<?php endif; ?>
															<?php if ( ! empty( $feature['heading'] ) ) : ?>
																<h4><?php echo esc_html( $feature['heading'] ); ?></h4>
															<?php endif; ?>
															<?php if ( ! empty( $feature['copy'] ) ) : ?>
																<p><?php echo esc_html( $feature['copy'] ); ?></p>
															<?php endif; ?>
															<?php if ( ! empty( $feature['cta_link'] ) && ! empty( $feature['cta_label'] ) ) : ?>
																<a href="<?php echo esc_url( $feature['cta_link']['url'] ); ?>"
																   <?php echo ! empty( $feature['cta_link']['target'] ) ? 'target="' . esc_attr( $feature['cta_link']['target'] ) . '" rel="noopener"' : ''; ?>
																   class="btn btn--gradient btn--small">
																	<?php echo esc_html( $feature['cta_label'] ); ?>
																</a>
															<?php endif; ?>
														</div>
													</div>
												<?php endif; ?>

											</div>
										</div>
									</div>
								</li>
							<?php endif; ?>

						<?php endwhile; ?>
					<?php endif; ?>

					<?php /* Mobile-only CTA inside the list */ ?>
					<?php if ( $cta_label && $cta_link ) : ?>
						<li>
							<a href="<?php echo esc_url( $cta_link['url'] ); ?>"
							   <?php echo ! empty( $cta_link['target'] ) ? 'target="' . esc_attr( $cta_link['target'] ) . '" rel="noopener"' : ''; ?>
							   class="btn btn--gradient btn--small nav__cta-mobile">
								<?php echo esc_html( $cta_label ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>

				<?php /* Desktop CTA */ ?>
				<?php if ( $cta_label && $cta_link ) : ?>
					<a href="<?php echo esc_url( $cta_link['url'] ); ?>"
					   <?php echo ! empty( $cta_link['target'] ) ? 'target="' . esc_attr( $cta_link['target'] ) . '" rel="noopener"' : ''; ?>
					   class="btn btn--gradient btn--small site-header__cta">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				<?php endif; ?>

				<button class="nav__toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navList">
					<span class="nav__toggle-bars" aria-hidden="true">
						<span></span><span></span><span></span>
					</span>
				</button>
			</nav>

		</div>
	</div>
</header>

<nav class="rail-band" aria-label="Audience selector">
  <a class="rail-band__cell" href="/lumisol/commercial/">Commercial</a>
  <a class="rail-band__cell" href="/lumisol/domestic/">Domestic</a>
</nav>