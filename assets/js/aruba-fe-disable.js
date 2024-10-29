window.addEventListener('DOMContentLoaded',()=>{

    const deactivateArubaFatturazioneElettronica = document.getElementById('deactivate-aruba-fatturazione-elettronica');

    if(deactivateArubaFatturazioneElettronica){

        const {href} = deactivateArubaFatturazioneElettronica;

        const {aruba_fe_labels} = aruba_fe_text;

        const settings = aruba_fe_labels;
        const createModal = () => {

            const source = document.getElementById("fe-disable").innerHTML;
            const template = Handlebars.compile(source);
            const html = template(settings);

            const DIV = document.createElement('div');

            DIV.innerHTML = html;

            const close = DIV.querySelector('[data-close]');

            close.addEventListener('click',e => {
                e.preventDefault();
                DIV.remove();
            });

            const mantain = DIV.querySelector('[data-mantain]');

            mantain.addEventListener('click',e => {
                e.preventDefault();
                window.location.href = `${href}&mantain=1`;
            });

            const noMantain = DIV.querySelector('[data-nomantain]');

            noMantain.addEventListener('click',e => {
                e.preventDefault();
                window.location.href = `${href}&mantain=0`;

            });

            document.body.append(DIV);

        }


        deactivateArubaFatturazioneElettronica.addEventListener('click',(e)=>{

            e.preventDefault();

            createModal();

        })

    }

})