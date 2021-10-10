import { ApiService, ErrorMessage } from "./apiService";

export class AppointmentService {
    public static async create(subject: string, durations: Array<number>, onError?: (msg: ErrorMessage) => void): Promise<Appointment> {
        return await ApiService.Fetch("appointment/create", "POST", null, { subject, durations }, onError);
    }

    public static async get(token: string, onError?: (msg: ErrorMessage) => void): Promise<Appointment> {
        return await ApiService.Fetch("appointment", "GET", { token }, null, onError);
    }

    public static async getWithCalendar(token: string, onError?: (msg: ErrorMessage) => void): Promise<Appointment> {
        return await ApiService.Fetch("appointment/calendar", "GET", { token }, null, onError);
    }

    public static async getDays(token: string, year: number, month: number, onError?: (msg: ErrorMessage) => void): Promise<void> {
        return await ApiService.Fetch("appointment/days/" + year + '/' + month, "GET", { token }, null, onError);
    }

    public static async setAppointment(token: string, dates: Array<Date>, onError?: (msg: ErrorMessage) => void): Promise<boolean> {
        return await ApiService.Fetch("appointment", "POST", { token }, { dates }, onError);
    }
}

export class Appointment {
    id: number;
    token: string;
    subject: string;
    parts: Array<AppointmentPart>;
}

export class AppointmentPart {
    id: number;
    appointment_id: number;
    duration: number;
    fromDate: string;
    toDate: string;
}