<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Enums\ThemeMode;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // ── Branding ───────────────────────────────────────────────
            ->brandName('PlayMate')

            // ── Warna sesuai tema frontend ──────────────────────────────
            ->colors([
                'primary' => Color::hex('#F97316'),   // oranye frontend
                'danger'  => Color::hex('#D62B2B'),   // merah frontend
                'success' => Color::hex('#22c55e'),
                'warning' => Color::hex('#eab308'),
                'info'    => Color::hex('#3b82f6'),
                'gray'    => Color::hex('#6b7280'),
            ])

            // ── Dark mode sesuai frontend yang gelap ────────────────────
            ->defaultThemeMode(ThemeMode::Dark)

            // ── Font sesuai frontend ────────────────────────────────────
            ->font('Inter')

            // ── Sidebar ─────────────────────────────────────────────────
            ->sidebarCollapsibleOnDesktop()

            // ── Custom CSS agar sidebar & UI cocok tema #0D0D0D ─────────
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => $this->customStyles()
            )

            // ── Resources, Pages, Widgets ───────────────────────────────
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                AccountWidget::class,
            ])

            // ── Navigation groups ────────────────────────────────────────
            ->navigationGroups([
                'Manajemen Pengguna',
                'Event & Komunitas',
                'Konten Platform',
            ])

            // ── Middleware ───────────────────────────────────────────────
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }

    private function customStyles(): string
    {
        return <<<'HTML'
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            /* ── Sidebar background sesuai #0D0D0D frontend ── */
            .dark [data-sidebar] aside,
            .dark aside[data-sidebar],
            .fi-sidebar {
                background-color: #0D0D0D !important;
                border-right-color: #1E1E1E !important;
            }

            /* ── Topbar / header ── */
            .fi-topbar,
            .fi-topbar nav,
            header.fi-topbar {
                background-color: #111111 !important;
                border-bottom: 1px solid #1E1E1E !important;
            }

            /* ── Main content background ── */
            .fi-main,
            .fi-main-ctn,
            main.fi-main {
                background-color: #161616 !important;
            }

            /* ── Body background ── */
            .fi-body {
                background-color: #0D0D0D !important;
            }

            /* ── Brand / logo area ── */
            .fi-logo {
                font-family: 'Oswald', sans-serif !important;
                font-weight: 700 !important;
                letter-spacing: 0.08em !important;
                text-transform: uppercase !important;
                color: #F97316 !important;
                font-size: 1.2rem !important;
            }

            /* ── Navigation item aktif: oranye ── */
            .fi-sidebar-item-active .fi-sidebar-item-button,
            .fi-sidebar-item .fi-sidebar-item-button.fi-active {
                background-color: rgba(249,115,22,0.12) !important;
                color: #F97316 !important;
            }
            .fi-sidebar-item-active .fi-sidebar-item-button svg,
            .fi-sidebar-item .fi-sidebar-item-button.fi-active svg {
                color: #F97316 !important;
            }

            /* ── Navigation group label ── */
            .fi-sidebar-group-label {
                font-family: 'Oswald', sans-serif !important;
                letter-spacing: 0.18em !important;
                text-transform: uppercase !important;
                font-size: 0.62rem !important;
                color: rgba(255,255,255,0.35) !important;
            }

            /* ── Sidebar item hover ── */
            .fi-sidebar-item-button:hover {
                background-color: rgba(249,115,22,0.07) !important;
                color: #F97316 !important;
            }

            /* ── Page heading / title font ── */
            .fi-header-heading,
            .fi-page-header h1 {
                font-family: 'Oswald', sans-serif !important;
                font-weight: 600 !important;
                letter-spacing: 0.05em !important;
                text-transform: uppercase !important;
            }

            /* ── Stat card border accent ── */
            .fi-stats-overview-stat {
                border-top: 2px solid #F97316 !important;
            }

            /* ── Table header ── */
            .fi-ta-header-cell {
                font-family: 'Oswald', sans-serif !important;
                letter-spacing: 0.1em !important;
                text-transform: uppercase !important;
                font-size: 0.72rem !important;
            }

            /* ── Buttons primary: oranye ── */
            .fi-btn-primary {
                background-color: #F97316 !important;
            }
            .fi-btn-primary:hover {
                background-color: #EA580C !important;
            }
        </style>
        HTML;
    }
}
