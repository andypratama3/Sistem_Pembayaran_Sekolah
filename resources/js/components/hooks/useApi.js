import { useState, useCallback, useEffect } from 'react';

/**
 * Custom hook for API calls with loading and error states
 */
export const useApi = () => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const request = useCallback(async (url, options = {}) => {
    setLoading(true);
    setError(null);

    try {
      const defaultOptions = {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
      };

      const response = await fetch(url, {
        ...defaultOptions,
        ...options,
        headers: {
          ...defaultOptions.headers,
          ...options.headers,
        },
      });

      if (!response.ok) {
        let errorMessage = `Request failed with status ${response.status}`;
        try {
          const errorData = await response.json();
          errorMessage = errorData.message || errorMessage;
        } catch (e) {
          // Response is not JSON, use status message
        }
        throw new Error(errorMessage);
      }

      const data = await response.json();
      setLoading(false);
      return data;
    } catch (err) {
      setLoading(false);
      const errorMessage = err.message || 'An error occurred';
      setError(errorMessage);
      throw err;
    }
  }, []);

  const get = useCallback((url) => request(url, { method: 'GET' }), [request]);

  const post = useCallback((url, body) => {
    return request(url, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }, [request]);

  const put = useCallback((url, body) => {
    return request(url, {
      method: 'PUT',
      body: JSON.stringify(body),
    });
  }, [request]);

  const del = useCallback((url) => {
    return request(url, { method: 'DELETE' });
  }, [request]);

  return {
    loading,
    error,
    get,
    post,
    put,
    delete: del,
    request,
  };
};

/**
 * Custom hook for debouncing values
 */
export const useDebounce = (value, delay = 500) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => clearTimeout(handler);
  }, [value, delay]);

  return debouncedValue;
};

/**
 * Custom hook for managing table state
 */
export const useTableState = (initialPageSize = 10) => {
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize] = useState(initialPageSize);
  const [sortConfig, setSortConfig] = useState(null);
  const [filters, setFilters] = useState({});

  const updateSort = useCallback((field, direction) => {
    setSortConfig({ field, direction });
    setCurrentPage(1); // Reset to first page when sorting
  }, []);

  const updateFilters = useCallback((newFilters) => {
    setFilters(newFilters);
    setCurrentPage(1); // Reset to first page when filtering
  }, []);

  const resetPagination = useCallback(() => {
    setCurrentPage(1);
  }, []);

  return {
    currentPage,
    setCurrentPage,
    pageSize,
    sortConfig,
    updateSort,
    filters,
    updateFilters,
    resetPagination,
  };
};
