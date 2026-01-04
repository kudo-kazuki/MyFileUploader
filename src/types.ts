export interface AdminJWTPayload {
    sub: string
    role: 'admin'
    exp: number
    iat: number
}

export type FileItem = {
    name: string
    extension: string
    updatedAt: number
    updatedAtIso: string
    fullPath: string
}
