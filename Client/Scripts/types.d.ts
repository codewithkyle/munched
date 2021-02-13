type Method = "GET" | "POST" | "PUT" | "HEAD" | "DELETE";

interface FetchReponse {
    success: boolean;
    data: any;
    error: string;
}

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
    User: User;
}

type User = {
    Name: string;
    Email: string;
    Uid: string;
    Groups: string[];
    Suspended: boolean;
    Verified: boolean;
    Admin: boolean;
};
interface UsersResponse extends ResponseCore {
    Users: Array<User>;
    Total: number;
}

interface ImpersonationLinkResponse extends ResponseCore {
    URL: string;
}
