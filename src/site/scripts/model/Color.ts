export class Color {

    private _r: number;
    public get r(): number { return this._r; }
    private _g: number;
    public get g(): number { return this._g; }
    private _b: number;
    public get b(): number { return this._b; }

    private _h: number;
    public get h(): number { return this._h; }
    private _s: number;
    public get s(): number { return this._s; }
    private _l: number;
    public get l(): number { return this._l; }

    private _a: number;
    public get a(): number { return this._a; }

    constructor() {
        this._r = this._g = this._b = 0;
        this._h = this._s = this._l = 0;
        this._a = 1;
    };

    /** RGB */
    public toCssRGB(): string {
        return `rgb(${Math.round(255 * this._r)},${Math.round(255 * this._g)},${Math.round(255 * this._b)})`;
    };

    public toCssRGBA(): string {
        return `rgba(${Math.round(255 * this._r)},${Math.round(255 * this._g)},${Math.round(255 * this._b)},${this._a})`;
    };

    /** HSL */
    public toCssHSL(): string {
        return `hsl(${Math.round(360 * this._h)},${Math.round(100 * this._s)}%,${Math.round(100 * this._l)}%)`;
    };

    public toCssHSLA(): string {
        return `hsla(${Math.round(360 * this._h)},${Math.round(100 * this._s)}%,${Math.round(100 * this._l)}%,${Math.round(this._a)})`;
    };

    /** HEX */
    public toCssHEX(): string {
        return "#" +
            Math.round(255 * this._r).toString(16).padStart(2, '0') +
            Math.round(255 * this._g).toString(16).padStart(2, '0') +
            Math.round(255 * this._b).toString(16).padStart(2, '0');
    };

    /** Modifiers */
    public saturate(v: string | number): Color {
        if ("string" == typeof v && v.indexOf("%") > -1 && (v = parseInt(v)) != NaN)
            this._s += v / 100;
        else if ("number" == typeof v) // range 255 
            this._s += v / 255;
        else throw new Error("error: bad modifier format (percent or number)");
        if (this._s > 1) this._s = 1; else if (this._s < 0) this._s = 0;
        this.updateRGBByHSL();

        return this;
    };

    public desaturate(v: string | number): Color {
        if ("string" == typeof v)
            return this.saturate("-" + v);
        if ("number" == typeof v)
            return this.saturate(-v);
        else
            throw new Error("error: bad modifier format (percent or number)");
    };

    public lighten(v: string | number): Color {
        if ("string" == typeof v && v.indexOf("%") > -1 && (v = parseInt(v)) != NaN)
            this._l += v / 100;
        else if ("number" == typeof v) // range 255 
            this._l += v / 255;
        else throw new Error("error: bad modifier format (percent or number)");
        if (this._l > 1) this._l = 1; else if (this._l < 0) this._l = 0;
        this.updateRGBByHSL();

        return this;
    };

    public darken(v: string | number): Color {
        if ("string" == typeof v)
            return this.lighten("-" + v);
        if ("number" == typeof v)
            return this.lighten(-v);
        else
            throw new Error("error: bad modifier format (percent or number)");
    };

    public fadein(v: string | number): Color {
        if ("string" == typeof v && v.indexOf("%") > -1 && (v = parseInt(v)) != NaN)
            this._a += v / 100;
        else if ("number" == typeof v) {
            if (Math.abs(v) > 1) // range 255 
                this._a += v / 255;
            else
                this._a += v;
        }
        else
            throw new Error("error: bad modifier format (percent or number)");
        if (this._a > 1) this._a = 1; else if (this._a < 0) this._a = 0;

        return this;
    };

    public fadeout(v: string | number): Color {
        if ("string" == typeof v)
            return this.fadein("-" + v);
        if ("number" == typeof v)
            return this.fadein(-v);
        else
            throw new Error("error: bad modifier format (percent or number)");
    };

    public spin(v: string | number): Color {
        if ("string" == typeof v && v.indexOf("%") > -1 && (v = parseInt(v)) != NaN)
            this._h += v / 100;
        else if ("number" == typeof v) // range 360 
            this._h += v / 360;
        else throw new Error("error: bad modifier format (percent or number)");
        if (this._h > 1) this._h = 1; else if (this._h < 0) this._h = 0;
        this.updateRGBByHSL();
        return this;
    };

    public static fromRGB(...args: any[]): Color {
        const c: Color = new Color();
        let sanitized: Array<number>;
        if (arguments.length < 3 || arguments.length > 4)
            throw new Error("error: 3 or 4 arguments");
        sanitized = Color.Sanitizer.RGB(arguments[0], arguments[1], arguments[2]);
        c._r = sanitized[0];
        c._g = sanitized[1];
        c._b = sanitized[2];
        if (arguments.length == 4) c._a = arguments[3];
        c.updateHSLByRGB();
        return c;
    };

    public static fromHSL(...args: Array<number | string>): Color {
        const c: Color = new Color();
        let sanitized: Array<number>;
        if (arguments.length < 3 || arguments.length > 4)
            throw new Error("error: 3 or 4 arguments");
        sanitized = Color.Sanitizer.HSL(arguments[0], arguments[1], arguments[2]);
        c._h = sanitized[0];
        c._s = sanitized[1];
        c._l = sanitized[2];
        if (arguments.length == 4) c._a = arguments[3];
        c.updateRGBByHSL();
        return c;
    };

    public static fromHEX(value: string): Color {
        var c = new Color(),
            sanitized;
        // Edit Ika 2018-0308
        // Allow leading '#'
        if (value && value.startsWith('#'))
            value = value.substr(1);
        Color.Validator.checkHEX(value);
        if (value.length == 3) {
            sanitized = Color.Sanitizer.RGB(
                parseInt(value.substr(0, 1) + value.substr(0, 1), 16),
                parseInt(value.substr(1, 1) + value.substr(1, 1), 16),
                parseInt(value.substr(2, 1) + value.substr(2, 1), 16)
            );
        } else if (value.length == 6) {
            sanitized = Color.Sanitizer.RGB(
                parseInt(value.substr(0, 2), 16),
                parseInt(value.substr(2, 2), 16),
                parseInt(value.substr(4, 2), 16)
            );
        } else throw new Error("error: 3 or 6 arguments");
        c._r = sanitized[0];
        c._g = sanitized[1];
        c._b = sanitized[2];
        c.updateHSLByRGB();
        return c;
    };

    public static parse(str: string): Color {
        if (typeof str == 'undefined')
            return null;
        if ((str = str.trim().toLowerCase()).length == 0)
            return null;
        if (str.startsWith('#'))
            return Color.fromHEX(str.substring(1, str.length));
        if (str.startsWith('rgb')) {
            var parts = /^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(0\.\d+))?\)$/.exec(str);
            // [ str, r, g, b, a|undefined ]
            if (typeof parts[4] == 'undefined')
                return Color.fromRGB(parts[1], parts[2], parts[3]);
            else
                return Color.fromRGB(parts[1], parts[2], parts[3], parts[4]);
        }
        else
            throw "Unrecognized color format: " + str;
    };

    private static Sanitizer = {
        RGB: function (...args: any[]) {
            var o = [];
            if (arguments.length == 0) return;
            for (var i = 0; i < arguments.length; i++) {
                var c = arguments[i];
                if ("string" == typeof c && c.indexOf("%") > -1) {
                    if ((c = parseInt(c)) == NaN)
                        throw new Error("Bad format");
                    if (c < 0 || c > 100)
                        throw new Error("Bad format");
                    o[i] = c / 100;
                } else {
                    if ("string" == typeof c && (c = parseInt(c)) == NaN) throw new Error("Bad format");
                    if (c < 0) throw new Error("Bad format");
                    else if (c >= 0 && c <= 1) o[i] = c;
                    else if (c > 1 && c < 256) o[i] = c / 255;
                    else throw new Error("Bad format (" + c + ")");
                }
            }
            return o;
        },

        HSL: function (...args: Array<string | number>): Array<number> {
            if (arguments.length < 3 || arguments.length > 4) throw new Error("3 or 4 arguments required");
            var h = arguments[0],
                s = arguments[1],
                l = arguments[2];
            if ("string" == typeof h && (h = parseFloat(h)) == NaN) throw new Error("Bad format for hue");
            if (h < 0 || h > 360) throw new Error("Hue out of range (0..360)");
            else if ((("" + h).indexOf(".") > -1 && h > 1) || ("" + h).indexOf(".") == -1) h /= 360;
            if ("string" == typeof s && s.indexOf("%") > -1) {
                if ((s = parseInt(s)) == NaN)
                    throw new Error("Bad format for saturation");
                if (s < 0 || s > 100)
                    throw new Error("Bad format for saturation");
                s /= 100;
            } else if (s < 0 || s > 1) throw new Error("Bad format for saturation");
            if ("string" == typeof l && l.indexOf("%") > -1) {
                if ((l = parseInt(l)) == NaN)
                    throw new Error("Bad format for lightness");
                if (l < 0 || l > 100)
                    throw new Error("Bad format for lightness");
                l /= 100;
            } else if (l < 0 || l > 1) throw new Error("Bad format for lightness");
            return [h, s, l];
        }
    }; // ENd sanitizer

    private static Validator = {

        /**
         * Check a hexa color (without #)
         */
        checkHEX: function (value: any) {

            if (value.length != 6 && value.length != 3)
                throw new Error("Hexa color: bad length (" + value.length + ")," + value);
            value = value.toLowerCase();
            for (var i in value) {
                var c = value.charCodeAt(i);
                if (!((c >= 48 && c <= 57) || (c >= 97 && c <= 102)))
                    throw new Error("Hexa color: out of range for " + value + " at position " + i);
            }
        }
    };

    public clone() {
        return Color.fromRGB(this.r, this.g, this.b, this.a);
    };

    public lerpRGB(c: Color, t: number): Color {
        return Color.fromRGB(
            this.r + (c.r - this.r) * t,
            this.g + (c.g - this.g) * t,
            this.b + (c.b - this.b) * t,
            this.a + (c.a - this.a) * t);
    };

    /**
 * Calculates HSL Color
 * RGB must be normalized
 * Must be executed in a Color object context
 * http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript 
 */
    private updateHSLByRGB() {
        //     
        let max = Math.max(this.r, this.g, this.b);
        let min = Math.min(this.r, this.g, this.b);
        this._l = (max + min) / 2;
        if (max == min) {
            this._h = this._s = 0; // achromatic
        } else {
            let d = max - min;
            this._s = this.l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case this.r:
                    this._h = (this.g - this.b) / d + (this.g < this.b ? 6 : 0);
                    break;
                case this.g:
                    this._h = (this.b - this.r) / d + 2;
                    break;
                case this.b:
                    this._h = (this.r - this.g) / d + 4;
                    break;
            }
            this._h /= 6;
        }
    }

    /**
     * Calculates RGB color (nomalized)
     * HSL must be normalized
     * Must be executed in a Color object context
     * http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript
     */
    private updateRGBByHSL() {
        var h = this.h;
        var s = this.s;
        var l = this.l;

        if (s == 0) {
            this._r = this._g = this._b = l; // achromatic
        } else {
            var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            var p = 2 * l - q;
            this._r = Color.hue2rgb(p, q, h + 1 / 3);
            this._g = Color.hue2rgb(p, q, h);
            this._b = Color.hue2rgb(p, q, h - 1 / 3);
        }
    }

    private static hue2rgb(p: number, q: number, t: number): number {
        if (t < 0) t += 1;
        if (t > 1) t -= 1;
        if (t < 1 / 6) return p + (q - p) * 6 * t;
        if (t < 1 / 2) return q;
        if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
        return p;
    };
};
