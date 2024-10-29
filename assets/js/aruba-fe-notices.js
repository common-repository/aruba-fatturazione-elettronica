class ArubaFeNotices {

    constructor(dismiss) {

        this.node = dismiss;
        this.option = this.node.getAttribute('data-dismiss-key');
        this.nonce  = this.node.dataset.nonce;

    }

    setUp() {

        const d = this.node.querySelector('.notice-dismiss');

        if (!d) return;

        d.addEventListener('click', e => {
            this.close();
        })

    }

    async close() {
        try {
            const formData = new URLSearchParams();
            formData.append('notice_id', this.option);
            formData.append('_aruba_fe_dismiss_notice', this.nonce);
            const result = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'action=dismiss_notice&' + formData.toString()
            });

            this.node.remove();


        } catch (error) {
            console.log(error)
            return false;
        }
    }

}


window.addEventListener('DOMContentLoaded', () => {

    const dismissable = document.querySelectorAll('[data-dismiss="true"]');

    if (dismissable.length) {

        [...dismissable].map(d => {
            const arubaFeNotices = new ArubaFeNotices(d);
            arubaFeNotices.setUp();
        });

    }

})