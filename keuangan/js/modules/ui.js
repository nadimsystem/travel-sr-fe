export const uiMixin = {
    methods: {
        changeView(v) { 
            const map = {
                dashboard: 'dashboard',
                bookingManagement: 'booking_management',
                bookingTravel: 'booking_travel',
                bookingBus: 'booking_bus',
                dispatcher: 'dispatcher',
                manifest: 'manifest',
                assets: 'assets',
                routeManagement: 'route_management',
                packageShipping: 'package_shipping'
            };
            if (map[v]) {
                window.location.href = map[v];
            } else {
                this.view = v; 
            }
        },
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            if (this.isDarkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            localStorage.setItem('sutan_v10_dark', this.isDarkMode);
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch((e) => console.log(e));
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    this.isFullscreen = false;
                }
            }
        }
    }
};
