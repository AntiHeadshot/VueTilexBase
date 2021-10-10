import { ApiService, ErrorMessage } from "./apiService";

export class LoginService {
    public static async login(email: string, password: string, onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("login", "POST", null, { email: email, password: password }, onError);
    }

    public static async logout(onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("logout", "POST", null, null, onError);
    }

    public static async register(username: string, email: string, password: string, onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("register", "POST", null, { email: email, username: username, password: password }, onError);
    }
}