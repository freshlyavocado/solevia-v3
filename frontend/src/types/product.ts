export interface Product {
  id: number
  category_id: number
  brand_id: number
  name: string
  slug: string
  description: string
  price: number
  discount_price: number
  discount_percentage?: number
  thumbnail?: string
  brand?: Brand
  category?: { id: number, name: string, slug: string }
  images?: { id: number, image_url: string }[]
  variants?: { id: number, size: string, stock: number }[]
}

export interface Brand {
  id: number
  name: string
  slug: string
  logo_url?: string
  description?: string
}
