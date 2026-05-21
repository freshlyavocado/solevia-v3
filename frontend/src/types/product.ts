export interface Product {
  id: number
  category_id: number
  brand_id: number
  name: string
  slug: string
  description: string
  price: number
  discount_price: number
  thumbnail: string
}

export interface Brand {
  id: number
  name: string
  slug: string
  logo: string
}
