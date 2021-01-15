function getToken(): string {
    return `bearer ${localStorage.getItem("token")}`;
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

function Logout() {
    localStorage.clear();
    sessionStorage.clear();
}

function Notify(message: string) {
    snackbar({
        message: message,
    });
}
