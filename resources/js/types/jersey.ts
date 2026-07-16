export interface JerseyTemplate {
    id: number;
    name: string;
    sport: "Basketball" | "Soccer" | "Baseball" | "Volleyball" | "Esports";
    price: number;
    rating: number;
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
    | "approved";

export interface DesignRequest {
    id: number;
    template_id: number;
    template_name: string;
    template_image: string;
    template_price: number;
    team_name: string;
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    font_style: string | null;
    estimated_quantity: number;
    notes: string | null;
    logo_path?: string | null;
    status: DesignRequestStatus;
    created_at: string;
}
