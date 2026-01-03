import { defineStore } from 'pinia'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

export const useUserAuthStore = defineStore('userAuth', {})
