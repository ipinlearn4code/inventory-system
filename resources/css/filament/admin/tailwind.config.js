import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'corporate-blue': '#00529B',
                'action-orange': '#F37021',
                'clean-white': '#FFFFFF',
                'neutral-background': '#F2F4F7',
                'deep-gray-text': '#344054',
                'bright-blue': '#0073E6',
            },
        },
    },
}
