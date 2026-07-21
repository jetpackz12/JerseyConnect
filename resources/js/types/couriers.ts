export type CourierStatus = "active" | "inactive";

export interface Courier {
    id: number;
    name: string;
    /** The courier's own track-and-trace page. */
    site: string;
    status: CourierStatus;
    created_at: string;
    updated_at: string;
}

/**
 * Static mock list — mirrors the records managed in the Courier module
 * (Admin > Couriers). Swap for a real fetch (e.g. a shared `couriers` prop
 * or an API call) once that module is wired to an endpoint; the Order
 * module should end up reading from the same source of truth instead of
 * keeping its own copy.
 */
export const mockCouriers: Courier[] = [
    {
        id: 1,
        name: "JT Express",
        site: "https://www.jtexpress.ph/track-and-trace?waybillNo=&flag=1",
        status: "active",
        created_at: "2026-07-01T09:15:00Z",
        updated_at: "2026-07-01T09:15:00Z",
    },
    {
        id: 2,
        name: "Flash Express",
        site: "https://www.flashexpress.ph/fle/tracking",
        status: "active",
        created_at: "2026-07-01T09:15:00Z",
        updated_at: "2026-07-01T09:15:00Z",
    },
];

export function activeCouriers(couriers: Courier[] = mockCouriers): Courier[] {
    return couriers.filter((c) => c.status === "active");
}

export function getCourierById(
    id: number,
    couriers: Courier[] = mockCouriers,
): Courier | undefined {
    return couriers.find((c) => c.id === id);
}