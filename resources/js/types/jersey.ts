export interface JerseyTemplate {
    id: number;
    name: string;
    sport: 'Basketball' | 'Soccer' | 'Baseball' | 'Volleyball' | 'Esports';
    price: number;
    rating: number;
    badge?: 'New' | 'Bestseller' | 'Hot';
    primaryColor: string;
    secondaryColor: string;
    accentColor: string;
    imagePath: string;
}