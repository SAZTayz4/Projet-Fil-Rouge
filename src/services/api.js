import { API_ENDPOINTS, API_CONFIG } from '../config/api';

export const apiService = {
  // Authentification
  login: async (email, password) => {
    const response = await fetch(API_ENDPOINTS.AUTH.LOGIN, {
      ...API_CONFIG,
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
    return response.json();
  },

  register: async (name, email, password) => {
    const response = await fetch(API_ENDPOINTS.AUTH.REGISTER, {
      ...API_CONFIG,
      method: 'POST',
      body: JSON.stringify({ name, email, password })
    });
    return response.json();
  },

  // Utilisateur
  getProfile: async (userId) => {
    const response = await fetch(`${API_ENDPOINTS.USER.PROFILE}?user_id=${userId}`, {
      ...API_CONFIG,
      method: 'GET'
    });
    return response.json();
  },

  // Blog
  getArticles: async () => {
    const response = await fetch(API_ENDPOINTS.BLOG.ARTICLES, {
      ...API_CONFIG,
      method: 'GET'
    });
    return response.json();
  },

  // Drops
  getDrops: async () => {
    const response = await fetch(API_ENDPOINTS.DROPS.LIST, {
      ...API_CONFIG,
      method: 'GET'
    });
    return response.json();
  },

  // SpotCheck
  analyzeImage: async (imageFile) => {
    const formData = new FormData();
    formData.append('image', imageFile);

    const response = await fetch(API_ENDPOINTS.SPOTCHECK.ANALYZE, {
      method: 'POST',
      body: formData
    });
    return response.json();
  }
}; 