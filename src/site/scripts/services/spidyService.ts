import { ApiService, ErrorMessage } from "./apiService";

export enum SpidyRequestType {
    Buy,
    Sell,
}

export class SpidyPage {
    sellorbuy: SpidyRequestType;
    count: number;
    page: number;
    last_page: number;
    total: number;
    results: Result[];
}

export class Result {
    listing_datetime: string;
    datetime: Date;
    unit_price: number;
    quantity: number;
    listings: number;
}

export class SpidyTransaction {
    datetime: Date;
    unit_price: number;

    public get datetimeEpoc(): number {
        return this.datetime.getTime();
    }

    public constructor(init?: Partial<SpidyTransaction>) {
        Object.assign(this, init);
    }
}

export class SpidyService {

    private static path: string = "https://www.gw2spidy.com/api/v0.9/json/listings/";

    public static async getItemSell(itemId: number, onError?: (msg: ErrorMessage) => void): Promise<SpidyPage> {
        let pageNr: number = 0;
        let page = await ApiService.Fetch<SpidyPage>(SpidyService.path + `${itemId}/sell/${pageNr}`, "GET", null, onError);
        SpidyService.updateDates(page);
        return page;
    }

    public static async getItemBuy(itemId: number, onError?: (msg: ErrorMessage) => void): Promise<SpidyPage> {
        let pageNr: number = 0;
        let page = await ApiService.Fetch<SpidyPage>(SpidyService.path + `${itemId}/buy/${pageNr}`, "GET", null, onError);
        SpidyService.updateDates(page);
        return page;
    }

    private static updateDates(page: SpidyPage) {
        for (let result of page.results) {
            result.datetime = new Date(result.listing_datetime);
        }
    }

    public static getBuys(results: Result[]): SpidyTransaction[] {
        let last: Result = results[0];
        let array = [];

        for (const result of results) {
            if (last.unit_price < result.unit_price)
                array.push(new SpidyTransaction({ datetime: result.datetime, unit_price: last.unit_price }));
            last = result;
        }

        return array;
    }
}

