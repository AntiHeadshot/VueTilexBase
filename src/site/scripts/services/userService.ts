import { ApiService, ErrorMessage } from "./apiService";

export enum Privilege {
    User = 1,
    Moderator = 2,
    Admin = 3,
}

export class User {
    id: number;
    name: string;
    email: string;
    isActive: boolean;
    privilege: Privilege;
}

export class UserService {
    public static async getUser(onError?: (msg: ErrorMessage) => void): Promise<User> {
        return await ApiService.Fetch<User>("user", "GET", null, null, onError);
    }

    public static async getUserById(id: number, onError?: (msg: ErrorMessage) => void): Promise<User> {
        return await ApiService.Fetch<User>("user", "GET", { id: id }, null, onError);
    }

    public static async getUsers(batchNumber: number, batchSize: number, onError?: (msg: ErrorMessage) => void): Promise<Array<User>> {
        return await ApiService.Fetch<Array<User>>("users", "GET", { batchSize: batchSize, batchNumber: batchNumber }, null, onError);
    }

    public static async create(username: string, email: string, password: string, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user", "POST", null, { name: username, email: email, password: password }, onError);
    }

    public static async update(oldPassword: string, email: string, username: string, newPassword: string, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user", "POST", null, { oldPassword, email, username, newPassword: newPassword == "" ? null : newPassword }, onError);
    }

    public static async setState(id: number, isActive: boolean, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user/" + id, "POST", null, { isActive: isActive }, onError);
    }

    public static async setPrivilege(id: number, privilege: Privilege, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user/" + id, "POST", null, { privilege: privilege.valueOf() }, onError);
    }

    public static async delete(id: number, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user/" + id, "DELETE", null, null, onError);
    }
    
    public static async deleteThis(password: string, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("user", "DELETE", { password }, null, onError);
    }
}