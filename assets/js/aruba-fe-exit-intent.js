window.aruba_fe_preventUnload = false;

const beforeUnloadListener = (event) => {
    if(window.aruba_fe_preventUnload) {
        event.preventDefault();
        return (event.returnValue = "");
    }
};

addEventListener("beforeunload", beforeUnloadListener, { capture: true });

