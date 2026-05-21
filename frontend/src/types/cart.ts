export interface CartItem {
  id: number
  variant_id: number
  quantity: number
  variant: any
}

export interface Cart {
  id: number
  user_id: number
  items: CartItem[]
}
