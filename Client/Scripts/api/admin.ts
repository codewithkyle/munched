async function GetUsers(page: number = 0, limit: number = 10): Promise<UsersResponse> {
    const request = await apiRequest(`/v1/admin/users?p=${page}&limit=${limit}`);
    const fetchResponse = await request.json();
    const response: Partial<UsersResponse> = buildResponseCore(fetchResponse.success, request.status, fetchResponse.error);
    response.Users = fetchResponse.data;
    return response as UsersResponse;
}

