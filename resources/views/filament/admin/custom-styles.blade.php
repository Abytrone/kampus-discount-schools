<style>
    /* Custom styles for Filament Admin Panel */

    /* Consistent brand logo size for sidebar and login page */
    .fi-sidebar-header .fi-logo,
    .fi-simple-page .fi-logo,
    .fi-login-page .fi-logo {
        height: 4rem !important;
        width: auto !important;
        max-width: 350px !important;
    }

    .fi-sidebar-header .fi-logo img,
    .fi-simple-page .fi-logo img,
    .fi-login-page .fi-logo img {
        height: 100% !important;
        width: auto !important;
        object-fit: contain;
    }

    /* Ensure logo is visible in both light and dark modes (sidebar) */
    .fi-sidebar-header .fi-logo img[src*="logo.png"] {
        display: block;
    }

    .fi-sidebar-header .fi-logo img[src*="logo-white.png"] {
        display: block;
    }

    /* Ensure logo is visible in both light and dark modes (login page) */
    .fi-simple-page .fi-logo img[src*="logo.png"],
    .fi-login-page .fi-logo img[src*="logo.png"] {
        display: block;
    }

    .fi-simple-page .fi-logo img[src*="logo-white.png"],
    .fi-login-page .fi-logo img[src*="logo-white.png"] {
        display: block;
    }
</style>
