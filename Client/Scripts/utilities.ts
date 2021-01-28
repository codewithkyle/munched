function getToken(): string {
    let token = localStorage.getItem("token") || sessionStorage.getItem("token") || "";
    return `bearer ${token}`;
}

function buildHeaders(): Headers {
    return new Headers({
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: getToken(),
    });
}

function buildResponseCore(success: boolean, statusCode: number, error: string = null): ResponseCore {
    return {
        Success: success,
        StatusCode: statusCode,
        Error: error,
    };
}

function ClearStorage() {
    localStorage.clear();
    sessionStorage.clear();
}

function Notify(message: string) {
    snackbar({
        message: message,
    });
}

function debounce(func: Function, wait: number, immediate: boolean): Function {
    let timeout;
    return function () {
        const context = this,
            args = arguments;
        const later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

document.onkeydown = (event: KeyboardEvent) => {
    if (event instanceof KeyboardEvent) {
        const key = event.key.toLowerCase();
        if (key === "f5") {
            event.returnValue = false;
            return false;
        } else if (key === "r" && (event.ctrlKey || event.metaKey)) {
            event.returnValue = false;
            return false;
        }
    }
};
