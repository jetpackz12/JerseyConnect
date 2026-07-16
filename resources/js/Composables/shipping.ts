import type { ShippingZone } from "@/types/orders.ts";

interface ZoneConfig {
    label: string;
    isLocal: boolean;
    baseFee: number; // flat fee for the first jersey
    perItemFee: number; // added per additional jersey
    eta: string;
}

/**
 * Single source of truth for automated shipping fee computation.
 * Only "metro_iloilo" counts as local; every other zone is treated as
 * "beyond the local area" and requires a third-party courier + tracking.
 *
 * Rates are flat placeholders — swap in your real matrix (or fetch it
 * from the backend) once pricing is finalized.
 */
export const SHIPPING_ZONES: Record<ShippingZone, ZoneConfig> = {
    metro_iloilo: {
        label: "Metro Iloilo (Local)",
        isLocal: true,
        baseFee: 0,
        perItemFee: 0,
        eta: "1–2 days • in-house delivery",
    },
    panay_visayas: {
        label: "Rest of Panay / Nearby Visayas",
        isLocal: false,
        baseFee: 150,
        perItemFee: 15,
        eta: "3–5 days via courier",
    },
    other_visayas: {
        label: "Other Visayas",
        isLocal: false,
        baseFee: 220,
        perItemFee: 20,
        eta: "5–7 days via courier",
    },
    luzon: {
        label: "Luzon (incl. Metro Manila)",
        isLocal: false,
        baseFee: 280,
        perItemFee: 25,
        eta: "5–8 days via courier",
    },
    mindanao: {
        label: "Mindanao",
        isLocal: false,
        baseFee: 300,
        perItemFee: 25,
        eta: "7–10 days via courier",
    },
};

export function computeShippingFee(zone: ShippingZone, quantity: number): number {
    const config = SHIPPING_ZONES[zone];
    if (!config || quantity <= 0) return 0;
    return config.baseFee + config.perItemFee * Math.max(0, quantity - 1);
}

export function isLocalZone(zone: ShippingZone): boolean {
    return SHIPPING_ZONES[zone]?.isLocal ?? false;
}

export function formatCurrency(value: number): string {
    return `₱${value.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}