import { ApiService, ErrorMessage } from "./apiService";

export class CalendarService {
    public static async connect(onError?: (msg: ErrorMessage) => void): Promise<string> {
        return await ApiService.Fetch("calendar/auth", "GET", null, null, onError);
    }

    public static async remove(onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("calendar/kill", "POST", null, null, onError);
    }

    public static async update(onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("calendar/update", "POST", null, null, onError);
    }

    public static async set(id: number, isActive: boolean, isPrimary: boolean, onError?: (msg: ErrorMessage) => void): Promise<Calendar[]> {
        return await ApiService.Fetch("calendar/"+id, "POST", null, { isActive, isPrimary }, onError);
    }

    public static async get(onError?: (msg: ErrorMessage) => void): Promise<Calendar[]> {
        return await ApiService.Fetch("calendar", "GET", null, null, onError);
    }
}

export class Calendar {
    id: number;
    calendar: string;
    name: string;
    isActive: boolean;
    isPrimary: boolean;
}