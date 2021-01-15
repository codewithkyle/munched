const API_URL = "http://api.munched.local";

async function RegisterUser(email: string, password: string, name: string): Promise<boolean> {
    let success = false;
    const data = {
        email: email,
        password: password,
        name: name,
    };
    const request = await fetch(`${API_URL}/v1/register`, {
        method: "POST",
        body: JSON.stringify(data),
        headers: new Headers({
            "Content-Type": "application/json",
        }),
    });
    const response = await request.json();
    if (request.ok) {
        success = response.success;
    } else {
        const error = response?.error || "Something went wrong on the server.";
        console.error(error);
    }
    return success;
}

async function LoginUser(email: string, password: string): Promise<boolean> {
    let success = false;
    const data = {
        email: email,
        password: password,
    };
    const request = await fetch(`${API_URL}/v1/login`, {
        method: "POST",
        body: JSON.stringify(data),
        headers: new Headers({
            "Content-Type": "application/json",
        }),
    });
    const response = await request.json();
    if (request.ok) {
        success = response.success;
        if (response.success) {
            localStorage.setItem("token", response.data.token);
        }
    } else {
        const error = response?.error || "Something went wrong on the server.";
        console.error(error);
    }
    return success;
}

async function GetProfile(token: string) {
    const request = await fetch(`${API_URL}/v1/user/profile`, {
        method: "GET",
        headers: new Headers({
            "Content-Type": "application/json",
            Authorization: `bearer ${token}`,
        }),
    });
    const response = await request.json();
    if (request.ok) {
        console.log(response);
    } else {
        const error = response?.error || "Something went wrong on the server.";
        console.error(error);
    }
    return;
}
