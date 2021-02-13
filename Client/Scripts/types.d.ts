interface ResponseCore {
    Success: boolean;
    StatusCode: number;
    Error: string;
}

interface FormResponse extends ResponseCore {
    FieldErrors: string[];
}

interface LoginResponse extends FormResponse {
    IsPendingEmailVerificaiton: boolean;
}

interface ProfileResponse extends ResponseCore {
    Name: string;
    Email: string;
    UID: string;
}

type User = {
    name: string;
    email: string;
    uid: string;
    groups: string[];
    id: number;
    suspended: number;
    verified: number;
    admin: number;
};
interface UsersResponse extends ResponseCore {
    Users: Array<User>;
}

type Method = "GET" | "POST" | "PUT" | "HEAD" | "DELETE";
