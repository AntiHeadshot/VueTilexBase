import { Cookies } from "./cookies";

export abstract class ApiService {

    private static path: string = "api/v1.0/";
    private static authorization: string;

    private static _initialize = (() => {
        ApiService.authorization = Cookies.get("Authorization");
    })();

    public static setToken(token: string) {
        console.log(token);
        ApiService.authorization = token;
        Cookies.set("Authorization", token);
    }

    private static async Post(request: string, method: string, urlPayload?: any, payload?: any): Promise<Response> {

        let uri = ApiService.path + request;
        if (urlPayload) {
            uri += "?";

            for (let key in urlPayload)
                uri += `${key}=${encodeURIComponent(urlPayload[key])}&`;

            uri = uri.slice(0, uri.length - 1);
        }
        return await fetch(uri, new BaseRequestInfo(method, payload, this.authorization));
    }

    public static async Fetch<T>(request: string, method: string, urlPayload?: any, payload?: any, onError?: (msg: ErrorMessage) => void): Promise<T> {

        let response = await this.Post(request, method, urlPayload, payload);
        let text = await response.text();
        let data = text.length > 0 ? JSON.parse(text) : null;
        if (response.ok) {
            return <T>data;
        }
        else {
            if (onError)
                onError(<ErrorMessage>data);
            return null;
        }

    }
}

export class BaseRequestInfo implements RequestInit {
    public static credentials: RequestCredentials = "same-origin";
    public method: string;
    public body: BodyInit;
    public headers: any;

    constructor(method: string, payload?: any, authorization?: string) {
        this.method = method;
        if (payload) {
            this.body = JSON.stringify(payload);
        }
        if (authorization)
            this.headers = { 'Authorization': authorization };
    }
}

export class ErrorMessage {
    public message: string = "";
    public statusCode: number = -1;
    public path: string = "";
    public method: string = "";
    public parameters: Parameters = new Parameters;
}

export class Parameters {
    public GET: any = {};
    public POST: any = {};
}

class Mapping {
    /**
     * Checks if the given json object is type of a given instance (class/interface) type.
     * @param jsonObject Object to check.
     * @param instanceType The type to check for the object.
     * @returns true if object is of the given instance type; false otherwise.
     */
    public static isTypeOf<T>(jsonObject: Object, instanceType: { new(): T; }): boolean {
        // Check that all the properties of the JSON Object are also available in the Class.
        const instanceObject = new instanceType();
        for (let propertyName in instanceObject) {
            if (!jsonObject.hasOwnProperty(propertyName)) {
                // If any property in instance object is missing then we have a mismatch.
                return false;
            }
        }

        // All the properties are matching between object and the instance type.
        return true;
    };

    /**
     * Checks if the given json object is type of a given instance (class/interface) type.
     * @param jsonObject Object to check.
     * @param instanceType The type to check for the object.
     * @returns true if object is of the given instance type; false otherwise.
     */
    public static isCollectionTypeOf<T>(jsonObjectCollection: any[], instanceType: { new(): T; }): boolean {
        // Check that all the properties of the JSON Object are also available in the Class.

        const instanceObject = new instanceType();
        for (let jsonObject of jsonObjectCollection) {
            for (let propertyName in instanceObject) {
                if (!jsonObject.hasOwnProperty(propertyName)) {
                    // If any property in instance object is missing then we have a mismatch.
                    return false;
                }
            }
        }
        // All the properties are matching between object and the instance type.
        return true;
    };
}; // End of class: Mapping