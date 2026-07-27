export interface JerseyTemplate {
    id: number;
    name: string;
    sport: "Basketball" | "Soccer" | "Baseball" | "Volleyball" | "Esports";
    price: number;
    badge?: "New" | "Bestseller" | "Hot";
    primaryColor: string;
    secondaryColor: string;
    accentColor: string;
    imagePath: string;
}

export type DesignRequestStatus =
    | "pending_review"
    | "in_discussion"
    | "revision_requested"
    | "waiting_for_down_payment"
    | "pending_down_payment_review"
    | "approved"
    | "cancelled";

export interface DesignRequest {
    id: number;
    template_id: number;
    template_name: string;
    template_image: string;
    template_image_url?: string;
    original_template_image?: string;
    template_price: number;
    team_name: string;
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    font_style: string | null;
    estimated_quantity: number;
    notes: string | null;
    logo_path?: string | null;
    logo_url?: string | null;
    status: DesignRequestStatus;
    created_at: string;
    gcash_number?: string | null;
    reference_number?: string | null;
    proof_image_url?: string | null;
}