export function modal() {
    return {
        show: false,
        open() {
            this.show = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.show = false;
            document.body.style.overflow = '';
        }
    };
}
