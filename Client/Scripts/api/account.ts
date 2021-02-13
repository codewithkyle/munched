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
    const fetchResponse = await request.json();
    const response: Partial<ProfileResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    if (response.Success) {
        response.Name = fetchResponse.data.name;
        response.Email = fetchResponse.data.email;
        response.UID = fetchResponse.data.uid;
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