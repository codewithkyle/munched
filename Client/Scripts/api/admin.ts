async function GetUsers(page: number = 0, limit: number = 10): Promise<UsersResponse> {
    const request = await apiRequest(`/v1/admin/users?p=${page}&limit=${limit}`);
    const fetchResponse:FetchReponse = await request.json();
    const response: Partial<UsersResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    response.Users = [];
    for (let i = 0; i < fetchResponse.data.users.length; i++){
        const user = fetchResponse.data.users[i];
        response.Users.push({
            Name: user.name,
            Email: user.email,
            Uid: user.uid,
            Groups: user.groups,
            Suspended: user.suspended,
            Verified: user.verified,
            Admin: user.admin,
        });
    }
    response.Total = fetchResponse.data.total;
    return response as UsersResponse;
}

