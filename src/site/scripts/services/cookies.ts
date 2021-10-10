export class Cookies {

    public static set(name: string, val: string) {
        const date = new Date();
        const value = val;

        // Set it expire in 14 days
        date.setTime(date.getTime() + (14 * 24 * 60 * 60 * 1000));

        // Set it
        document.cookie = `${name}=${value};expires=${date.toUTCString()};SameSite=Strict;path=/`;
    }

    public static get(name: string) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);

        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    }

    public static delete(name: string) {
        const date = new Date();

        // Set it expire in -1 days
        date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));

        // Set it
        document.cookie = `${name}=;expires=${date.toUTCString()};SameSite=Strict;path=/`;
    }

    public static deleteAll(){
        const date = new Date();

        // Set it expire in -1 days
        date.setTime(date.getTime() + (-1 * 24 * 60 * 60 * 1000));

        document.cookie = `$expires=${date.toUTCString()};SameSite=Strict;path=/`;
    }
}