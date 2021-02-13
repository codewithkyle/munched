async function UpdateEmailAddress(email: string): Promise<ResponseCore> {
    const data = {
        email: email,
    };
    const request = await apiRequest("/v1/user/update-email", "POST", data);
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function GetProfile(): Promise<ProfileResponse> {
    const request = await apiRequest("/v1/user/profile");
    const fetchResponse:FetchReponse = await request.json();
    const response: Partial<ProfileResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (response.Success) {
        response.User = {
            Name: fetchResponse.data.name,
            Email: fetchResponse.data.email,
            Uid: fetchResponse.data.uid,
            Groups: fetchResponse.data.groups,
            Suspended: fetchResponse.data.suspended,
            Verified: fetchResponse.data.verified,
            Admin: fetchResponse.data.admin,
        };
    }
    return response as ProfileResponse;
}

async function UpdateProfile(name: string): Promise<ResponseCore> {
    const data = {
        name: name,
    };
    const request = await apiRequest("/v1/user/profile", "POST", data);
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}

async function UpdatePassword(oldPassword: string, newPassword: string): Promise<ResponseCore> {
    const data = {
        oldPassword: oldPassword,
        newPassword: newPassword,
    };
    const request = await apiRequest("/v1/user/update-password", "POST", data);
    const fetchResponse = await request.json();
    const response = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    return response;
}