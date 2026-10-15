/**
 * Alpine data for the admin frame: the mobile treatments drawer.
 */
export function adminShell() {
    return {
        drawerOpen: false,

        openDrawer() {
            this.drawerOpen = true;
        },

        closeDrawer() {
            this.drawerOpen = false;
        },
    };
}
