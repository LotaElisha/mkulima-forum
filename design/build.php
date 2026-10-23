<?php

/**
 * Writes public/css/mk-tokens.css and mkulima_app/lib/core/mk_tokens.dart
 * from design/tokens.json. Run: php design/build.php
 */
$root = dirname(__DIR__);
$tokens = json_decode(file_get_contents($root.'/design/tokens.json'), true, 512, JSON_THROW_ON_ERROR);

$c = [];
foreach ($tokens['color'] as $name => $spec) {
    $c[$name] = $spec['value'];
}

$css = <<<CSS
/* Generated from design/tokens.json. Do not edit by hand. */
:root {
  --mk-font: {$tokens['font']['family']};
  --mk-color-primary: {$c['primary']};
  --mk-color-on-primary: {$c['onPrimary']};
  --mk-color-primary-dark: {$c['primaryDark']};
  --mk-color-leaf: {$c['leaf']};
  --mk-color-leaf-pale: {$c['leafPale']};
  --mk-color-ink: {$c['ink']};
  --mk-color-ink-body: {$c['inkBody']};
  --mk-color-muted: {$c['muted']};
  --mk-color-surface: {$c['surface']};
  --mk-color-surface-muted: {$c['surfaceMuted']};
  --mk-color-border: {$c['border']};
  --mk-color-border-strong: {$c['borderStrong']};
  --mk-color-danger: {$c['danger']};
  --mk-color-danger-soft: {$c['dangerSoft']};
  --mk-color-warning: {$c['warning']};
  --mk-color-warning-soft: {$c['warningSoft']};
  --mk-color-info: {$c['info']};
  --mk-color-info-soft: {$c['infoSoft']};
  --mk-color-earth: {$c['earth']};

  --mk-text-caption: {$tokens['fontSize']['caption']}px;
  --mk-text-body: {$tokens['fontSize']['body']}px;
  --mk-text-title: {$tokens['fontSize']['title']}px;
  --mk-text-page: {$tokens['fontSize']['page']}px;
  --mk-text-display: {$tokens['fontSize']['display']}px;

  --mk-space-1: {$tokens['space']['1']}px;
  --mk-space-2: {$tokens['space']['2']}px;
  --mk-space-3: {$tokens['space']['3']}px;
  --mk-space-4: {$tokens['space']['4']}px;
  --mk-space-5: {$tokens['space']['5']}px;
  --mk-space-6: {$tokens['space']['6']}px;
  --mk-space-8: {$tokens['space']['8']}px;
  --mk-space-12: {$tokens['space']['12']}px;

  --mk-radius-sm: {$tokens['radius']['sm']}px;
  --mk-radius-md: {$tokens['radius']['md']}px;
  --mk-radius-lg: {$tokens['radius']['lg']}px;
  --mk-radius-xl: {$tokens['radius']['xl']}px;
  --mk-radius-full: {$tokens['radius']['full']}px;

  --mk-shadow-1: {$tokens['elevation']['1']};
  --mk-shadow-2: {$tokens['elevation']['2']};
  --mk-motion-fast: {$tokens['motion']['fast']};
  --mk-motion: {$tokens['motion']['base']};
  --mk-tap: {$tokens['size']['tap']}px;
  --mk-button: {$tokens['size']['button']}px;
  --mk-nav: {$tokens['size']['nav']}px;

  /* Names the existing Blade pages already use. */
  --forest-dark: var(--mk-color-primary-dark);
  --forest-mid: var(--mk-color-primary);
  --forest-light: var(--mk-color-primary);
  --leaf-green: var(--mk-color-leaf);
  --leaf-bright: var(--mk-color-leaf);
  --leaf-pale: var(--mk-color-leaf-pale);
  --sun-gold: var(--mk-color-warning);
  --sun-amber: var(--mk-color-earth);
  --accent-soft: var(--mk-color-warning-soft);
  --cream-bg: var(--mk-color-surface);
  --cream-card: var(--mk-color-surface);
  --surface: var(--mk-color-surface);
  --surface-card: var(--mk-color-surface);
  --surface-soft: var(--mk-color-surface-muted);
  --surface-sunken: var(--mk-color-surface-muted);
  --ink-dark: var(--mk-color-ink);
  --ink-body: var(--mk-color-ink-body);
  --ink-muted: var(--mk-color-muted);
  --ink-faint: var(--mk-color-muted);
  --border-light: var(--mk-color-border);
  --border-mid: var(--mk-color-border-strong);
  --line: var(--mk-color-border);
  --radius-2xl: var(--mk-radius-xl);
  --radius-xl: var(--mk-radius-lg);
  --radius-lg: var(--mk-radius-md);
  --radius-md: var(--mk-radius-sm);
  --shadow-xs: var(--mk-shadow-1);
  --shadow-sm: var(--mk-shadow-1);
  --shadow-md: var(--mk-shadow-2);
  --shadow-lg: var(--mk-shadow-2);
  --nav-h: var(--mk-nav);

  --green-700: var(--mk-color-primary-dark);
  --green-600: var(--mk-color-primary);
  --green-500: var(--mk-color-primary);
  --green-50: var(--mk-color-leaf-pale);
  --green-100: var(--mk-color-leaf-pale);
  --ink: var(--mk-color-ink);
  --line-strong: var(--mk-color-border-strong);
  --warn: var(--mk-color-warning);
  --danger: var(--mk-color-danger);
  --danger-bg: var(--mk-color-danger-soft);
  --ok: var(--mk-color-primary-dark);
  --ok-bg: var(--mk-color-leaf-pale);
  --r-sm: var(--mk-radius-sm);
  --r-md: var(--mk-radius-md);
  --r-lg: var(--mk-radius-lg);
}

CSS;

if (! is_dir($root.'/public/css')) {
    mkdir($root.'/public/css', 0775, true);
}
file_put_contents($root.'/public/css/mk-tokens.css', $css);

$hex = static function (string $value): string {
    return '0xFF'.strtoupper(ltrim($value, '#'));
};

$dartColors = '';
foreach ($tokens['color'] as $name => $spec) {
    $dartColors .= '  static const Color color'.ucfirst($name).' = Color('.$hex($spec['value']).");\n";
}

$dart = <<<DART
// Generated from design/tokens.json. Do not edit by hand.
import 'package:flutter/material.dart';

class MkTokens {
  MkTokens._();

$dartColors
  static const String fontFamily = 'Roboto';

  static const double textCaption = {$tokens['fontSize']['caption']};
  static const double textBody = {$tokens['fontSize']['body']};
  static const double textLabel = {$tokens['fontSize']['label']};
  static const double textTitle = {$tokens['fontSize']['title']};
  static const double textPage = {$tokens['fontSize']['page']};
  static const double textDisplay = {$tokens['fontSize']['display']};

  static const double space1 = {$tokens['space']['1']};
  static const double space2 = {$tokens['space']['2']};
  static const double space3 = {$tokens['space']['3']};
  static const double space4 = {$tokens['space']['4']};
  static const double space6 = {$tokens['space']['6']};
  static const double space8 = {$tokens['space']['8']};

  static const double radiusSm = {$tokens['radius']['sm']};
  static const double radiusMd = {$tokens['radius']['md']};
  static const double radiusLg = {$tokens['radius']['lg']};
  static const double radiusXl = {$tokens['radius']['xl']};

  static const double tap = {$tokens['size']['tap']};
  static const double button = {$tokens['size']['button']};
}

DART;

file_put_contents($root.'/mkulima_app/lib/core/mk_tokens.dart', $dart);

echo "Wrote public/css/mk-tokens.css and mkulima_app/lib/core/mk_tokens.dart\n";
