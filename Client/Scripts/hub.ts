const API_URL = "http://api.munched.local";

interface ResponseCore {
    Success: boolean;
    StatusCode: number;
    Error: string;
}

interface FormResponse extends ResponseCore {
    FieldErrors: string[];
}

async function RegisterUser(email: string, password: string, name: string): Promise<FormResponse> {
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
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<FormResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (!response.Success) {
        response.FieldErrors = fetchResponse.data;
    }
    return response as FormResponse;
}

interface LoginResponse extends FormResponse {
    IsPendingEmailVerificaiton: boolean;
}
async function LoginUser(email: string, password: string, trustDevice: boolean): Promise<LoginResponse> {
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
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<LoginResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (response.Success) {
        if (!trustDevice) {
            window.onbeforeunload = (e: Event) => {
                ClearStorage();
                fetch(`${API_URL}/v1/logout`, {
                    method: "POST",
                    headers: buildHeaders(),
                    keepalive: true,
                    credentials: "include",
                });
            };
        }
        response.IsPendingEmailVerificaiton = fetchResponse.data.pendingEmailVerification;
    } else {
        response.FieldErrors = fetchResponse.data;
        response.IsPendingEmailVerificaiton = false;
    }
    return response as LoginResponse;
}

interface ProfileResponse extends ResponseCore {
    Name: string;
    Email: string;
    UID: string;
}
async function GetProfile(): Promise<ProfileResponse> {
    const request = await fetch(`${API_URL}/v1/user/profile`, {
        method: "GET",
        headers: buildHeaders(),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<ProfileResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (response.Success) {
        response.Name = fetchResponse.data.name;
        response.Email = fetchResponse.data.email;
        response.UID = fetchResponse.data.uid;
    }
    return response as ProfileResponse;
}

async function ResendVerificationEmail(): Promise<ResponseCore> {
    const request = await fetch(`${API_URL}/v1/resend-verification-email`, {
        method: "POST",
        headers: buildHeaders(),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    return buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
}

async function RefreshToken(): Promise<ResponseCore> {
    const request = await fetch(`${API_URL}/v1/refresh-token`, {
        method: "POST",
        headers: buildHeaders(),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function Logout() {
    const request = await fetch(`${API_URL}/v1/logout`, {
        method: "POST",
        headers: buildHeaders(),
        credentials: "include",
    });
    if (request.ok){
        ClearStorage();
        location.href = location.origin;
    } else {
        console.error(`Failed to log out user on the server. ${request.status}: ${request.statusText}`);
        location.href = location.origin;
    }
}

async function UpdateEmailAddress(email: string): Promise<ResponseCore> {
    const data = {
        email: email,
    };
    const request = await fetch(`${API_URL}/v1/user/update-email`, {
        method: "POST",
        headers: buildHeaders(),
        body: JSON.stringify(data),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function UpdateProfile(name: string): Promise<ResponseCore> {
    const data = {
        name: name,
    };
    const request = await fetch(`${API_URL}/v1/user/profile`, {
        method: "POST",
        headers: buildHeaders(),
        body: JSON.stringify(data),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function UpdatePassword(oldPassword: string, newPassword: string): Promise<ResponseCore> {
    const data = {
        oldPassword: oldPassword,
        newPassword: newPassword,
    };
    const request = await fetch(`${API_URL}/v1/user/update-password`, {
        method: "POST",
        headers: buildHeaders(),
        body: JSON.stringify(data),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function ForgotPassword(email: string): Promise<FormResponse> {
    const data = {
        email: email,
    };
    const request = await fetch(`${API_URL}/v1/forgot-password`, {
        method: "POST",
        body: JSON.stringify(data),
        headers: new Headers({
            "Content-Type": "application/json",
        }),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<FormResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (!response.Success) {
        response.FieldErrors = fetchResponse.data;
    }
    return response as FormResponse;
}

async function ResetPassword(password: string, verificationCode: string): Promise<FormResponse> {
    const data = {
        password: password,
        code: verificationCode,
    };
    const request = await fetch(`${API_URL}/v1/reset-password`, {
        method: "POST",
        body: JSON.stringify(data),
        headers: new Headers({
            "Content-Type": "application/json",
        }),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<FormResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (!response.Success) {
        response.FieldErrors = fetchResponse.data;
    }
    return response as FormResponse;
}

async function VerifyAdmin(): Promise<ResponseCore> {
    const request = await fetch(`${API_URL}/v1/admin/verify`, {
        method: "GET",
        headers: buildHeaders(),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

type User = {};
interface UsersResponse extends ResponseCore {
    Users: Array<User>;
}
async function GetUsers(page: number = 0, limit: number = 10): Promise<UsersResponse> {
    const request = await fetch(`${API_URL}/v1/admin/users?p=${page}&limit=${limit}`, {
        method: "GET",
        headers: buildHeaders(),
        credentials: "include",
    });
    const fetchResponse = await request.json();
    const response: Partial<UsersResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    response.Users = fetchResponse.data;
    return response as UsersResponse;
}
