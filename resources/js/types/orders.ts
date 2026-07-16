import type { DesignRequest } from "@/types/jersey";

/**
 * An Order is created the moment a DesignRequest becomes "approved".
 * The jersey design fields (template, colors, font, etc.) are copied
 * over at creation time so the order keeps a frozen snapshot of what
 * was actually approved, even if the customer edits the design again later.
 */
export type OrderStatus =
    | "processing" // order created, payment/details being confirmed
    | "in_production" // jerseys being printed/sewn
    | "ready_for_delivery" // production done, packed, waiting for courier drop-off
    | "shipped" // handed to courier, tracking info available
    | "delivered" // courier marked it delivered
    | "completed" // customer confirmed receipt / order closed
    | "cancelled";

/**
 * Shipping zone drives the automated shipping fee computation.
 * "metro_iloilo" is treated as the local area (no third-party courier needed).
 * Everything else is "beyond the local area" and requires a courier receipt.
 */
export type ShippingZone =
    | "metro_iloilo"
    | "panay_visayas"
    | "other_visayas"
    | "luzon"
    | "mindanao";

export interface Address {
    id?: number;
    recipient_name: string;
    contact_number: string;
    line1: string;
    line2?: string | null;
    barangay?: string | null;
    city: string;
    province: string;
    postal_code: string;
    zone: ShippingZone;
    is_default?: boolean;
}

/**
 * Since there is no courier API integration, the courier "tracking" is just
 * a manually-entered transaction/waybill number plus the public tracking
 * link the courier already exposes on their own website. Whoever marks an
 * order as "shipped" (admin) fills this in once, and the customer sees a
 * read-only "Track Package" link from then on.
 */
export interface CourierReceipt {
    id?: number;
    courier_name: string;
    transaction_number: string;
    tracking_url: string;
    date_shipped: string;
    remarks?: string | null;
}

export interface Order {
    id: number;
    order_number: string;
    design_request_id: number;

    // Frozen design snapshot (copied from the approved DesignRequest)
    template_name: string;
    template_image: string;
    team_name: string;
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    font_style: string;

    quantity: number;
    unit_price: number;

    address: Address;
    shipping_fee: number;

    status: OrderStatus;
    courier_receipt: CourierReceipt | null;

    created_at: string;
    updated_at: string;
}

export function buildOrderFromDesignRequest(
    request: DesignRequest,
    overrides: Partial<Order> = {},
): Partial<Order> {
    return {
        design_request_id: request.id,
        template_name: request.template_name,
        template_image: request.template_image,
        team_name: request.team_name,
        primary_color: request.primary_color,
        secondary_color: request.secondary_color,
        accent_color: request.accent_color,
        font_style: request.font_style ?? "",
        quantity: request.estimated_quantity,
        unit_price: request.template_price,
        ...overrides,
    };
}