<?php
/**
 * Header — Anchor Framework floating glass nav + full-screen mobile menu.
 *
 * Brand-agnostic shell. Logo, phone and business name come from Anchor Site Config
 * (never hardcoded). One nav array drives both the desktop pill nav (with dropdowns)
 * and the mobile overlay; child themes / sites override it via the `ac_primary_nav`
 * filter. Section anchors are absolute so they work from inner pages too.
 *
 * @package AnchorFramework
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ac_logo_white = trim( do_shortcode( '[config_site_image_white]' ) );
$ac_brand      = trim( do_shortcode( '[config_business_name]' ) );
$ac_phone      = trim( do_shortcode( '[config_phone]' ) );
$ac_phone_href = trim( do_shortcode( '[config_phone_href]' ) );

/**
 * Primary navigation. Parent ships a minimal generic default; the active
 * child theme / site supplies its real IA via the `ac_primary_nav` filter.
 */
$ac_default_nav = array(
	array( 'label' => 'Home', 'url' => home_url( '/' ) ),
	array( 'label' => 'Services', 'url' => home_url( '/#services' ) ),
	array( 'label' => 'About Us', 'url' => home_url( '/about-us/' ) ),
	array( 'label' => 'Contact Us', 'url' => home_url( '/contact-us/' ) ),
);
/** @var array $ac_nav */
$ac_nav = apply_filters( 'ac_primary_nav', $ac_default_nav );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Super header (utility bar): phone left, social right. Blue bar, white text.
$ac_topbar_social = ac_social_links_html( 'ac-topbar__social-link', 18 );
if ( $ac_phone || $ac_topbar_social ) :
	?>
<div class="ac-topbar">
	<div class="ac-container ac-topbar__inner">
		<?php if ( $ac_phone ) : ?>
			<a class="ac-topbar__phone" href="<?php echo esc_url( $ac_phone_href ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				<span class="ac-topbar__phone-num"><?php echo esc_html( $ac_phone ); ?></span>
				<span class="ac-topbar__phone-cta">Call now</span>
			</a>
		<?php else : ?>
			<span></span>
		<?php endif; ?>
		<span class="ac-topbar__hours">Open 24 Hours</span>
		<div class="ac-topbar__social"><?php echo $ac_topbar_social; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* internally. ?></div>
	</div>
</div>
	<?php
endif;
?>

<header class="ac-header" id="ac-header">
	<div class="ac-header__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ac-header__logo">
			<img id="ac-logo" src="<?php echo esc_url( $ac_logo_white ); ?>" alt="<?php echo esc_attr( $ac_brand ); ?>" class="ac-header__logo-img">
		</a>
		<nav id="ac-nav" class="ac-glass-nav" aria-label="Main navigation">
			<?php foreach ( $ac_nav as $ac_item ) : ?>
				<?php if ( empty( $ac_item['children'] ) ) : ?>
					<a href="<?php echo esc_url( $ac_item['url'] ); ?>"><?php echo esc_html( $ac_item['label'] ); ?></a>
				<?php else : ?>
					<div class="ac-nav-item">
						<a href="<?php echo esc_url( $ac_item['url'] ); ?>" class="ac-nav-item__top"><?php echo esc_html( $ac_item['label'] ); ?> <span class="ac-nav-caret" aria-hidden="true">▾</span></a>
						<div class="ac-nav-drop">
							<?php foreach ( $ac_item['children'] as $ac_child ) : ?>
								<a href="<?php echo esc_url( $ac_child['url'] ); ?>"><?php echo esc_html( $ac_child['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
		<a href="<?php echo esc_url( ac_contact_url() ); ?>" class="ac-header__cta">Free Estimate</a>
		<button id="ac-burger" class="ac-burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="ac-menu">
			<span></span><span></span><span></span>
		</button>
	</div>
</header>

<!-- Mobile full-screen menu -->
<div class="ac-menu" id="ac-menu" aria-hidden="true">
	<div class="ac-menu__viewport">
		<nav class="ac-menu__panel ac-menu__panel--root is-active" aria-label="Mobile navigation">
			<ul class="ac-menu__list">
				<?php foreach ( $ac_nav as $ac_i => $ac_item ) : ?>
					<li>
						<?php if ( empty( $ac_item['children'] ) ) : ?>
							<a href="<?php echo esc_url( $ac_item['url'] ); ?>"><?php echo esc_html( $ac_item['label'] ); ?></a>
						<?php else : ?>
							<div class="ac-menu__row">
								<a class="ac-menu__link" href="<?php echo esc_url( $ac_item['url'] ); ?>"><?php echo esc_html( $ac_item['label'] ); ?></a>
								<button type="button" class="ac-menu__into" data-target="ac-panel-<?php echo (int) $ac_i; ?>" aria-label="<?php echo esc_attr( 'Open ' . $ac_item['label'] . ' submenu' ); ?>"></button>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php foreach ( $ac_nav as $ac_i => $ac_item ) : ?>
			<?php if ( empty( $ac_item['children'] ) ) { continue; } ?>
			<nav class="ac-menu__panel ac-menu__panel--sub" id="ac-panel-<?php echo (int) $ac_i; ?>" aria-label="<?php echo esc_attr( $ac_item['label'] ); ?>">
				<button type="button" class="ac-menu__back" data-back>
					<span class="ac-menu__back-icon" aria-hidden="true"></span>
					<span class="ac-menu__back-label"><?php echo esc_html( $ac_item['label'] ); ?></span>
				</button>
				<ul class="ac-menu__list">
					<?php foreach ( $ac_item['children'] as $ac_child ) : ?>
						<li><a href="<?php echo esc_url( $ac_child['url'] ); ?>"><?php echo esc_html( $ac_child['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endforeach; ?>
	</div>
	<div class="ac-menu__footer">
		<a class="ac-menu__call" href="<?php echo esc_url( $ac_phone_href ); ?>" aria-label="Call <?php echo esc_attr( $ac_phone ); ?>">
			<span class="ac-menu__call-label">Call Now</span>
		</a>
		<a class="ac-btn ac-btn--solid ac-menu__est" href="<?php echo esc_url( ac_contact_url() ); ?>">Get a Free Estimate</a>
	</div>
</div>
