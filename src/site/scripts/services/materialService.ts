import { ApiService, ErrorMessage } from "./apiService";

export enum ItemType {
    Armor,// Armor
    Back,// Back item
    Bag,// Bags
    Consumable,// Consumables
    Container,// Containers
    CraftingMaterial,// Crafting materials
    Gathering,// Gathering tools
    Gizmo,// Gizmos
    Key,//
    MiniPet,// Miniatures
    Tool,// Salvage kits
    Trait,// Trait guides
    Trinket,// Trinkets
    Trophy,// Trophies
    UpgradeComponent,// Upgrade components
    Weapon,// Weapons
}

export enum ItemRarity {
    Junk,
    Basic,
    Fine,
    Masterwork,
    Rare,
    Exotic,
    Ascended,
    Legendary
}

export enum ItemGameType {
    Activity,// Usable in activities
    Dungeon,// Usable in dungeons
    Pve,// Usable in general PvE
    Pvp,// Usable in PvP
    PvpLobby,// Usable in the Heart of the Mists
    Wvw,// Usable in World vs. World
}

export enum ItemFlag {
    AccountBindOnUse,// Account bound on use
    AccountBound,// Account bound on acquire
    Attuned,// If the item is Attuned
    BulkConsume,// If the item can be bulk consumed
    DeleteWarning,// If the item will prompt the player with a warning when deleting
    HideSuffix,// Hide the suffix of the upgrade component
    Infused,// If the item is infused
    MonsterOnly,//
    NoMysticForge,// Not usable in the Mystic Forge
    NoSalvage,// Not salvageable
    NoSell,// Not sellable
    NotUpgradeable,// Not upgradeable
    NoUnderwater,// Not available underwater
    SoulbindOnAcquire,// Soulbound on acquire
    SoulBindOnUse,// Soulbound on use
    Tonic,// If the item is a tonic
    Unique,// Unique
}

export enum ItemRestriction {
    Asura,
    Charr,
    Female,
    Human,
    Norn,
    Sylvari,
    Elementalist,
    Engineer,
    Guardian,
    Mesmer,
    Necromancer,
    Ranger,
    Thief,
    Warrior
}

export class Material {
    id: number;
    name: string;
    items: number[];
    order: number;
}

export class Item {
    name: string;
    description: string;
    type: ItemType;
    level: number;
    rarity: ItemRarity;
    vendor_value: number;
    default_skin: number;
    game_types: ItemGameType[];
    flags: ItemFlag[];
    restrictions: ItemRestriction[];
    id: number;
    chat_link: string;
    icon: string;
}

export class MaterialService {

    private static path: string = "https://api.guildwars2.com/v2/";

    public static async getMaterials(onError?: (msg: ErrorMessage) => void): Promise<Array<Material>> {
        return await ApiService.Fetch<Array<Material>>(MaterialService.path + "materials?page=0&page_size=200", "GET", null, onError);
    }

    public static async getItems(itemIds: number[], onError?: (msg: ErrorMessage) => void): Promise<Array<Item>> {

        let itemGroups = itemIds.chunk(200);

        let items: Array<Item> = [];

        for (let group of itemGroups) {
            items = items.concat(await ApiService.Fetch<Array<Item>>(MaterialService.path + "items?ids=" + group.join(","), "GET", null, onError));
        }
        return items;
    }
}

declare global {
    interface Array<T> {
        chunk(n: number): Array<Array<T>>;
        min(): number;
        max(): number;
    }
}

Array.prototype.chunk = function (n) {
    return Array.from(Array(Math.ceil(this.length / n)).keys()).map((x, i) => this.slice(i * n, i * n + n));
}

Array.prototype.max = function () {
    return Math.max.apply(null, this);
};

Array.prototype.min = function () {
    return Math.min.apply(null, this);
};