// src/BulkPriceRules.js
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const BulkPriceRules = ({ fetchProducts }) => {
    const apiUrl = process.env.REACT_APP_API_URL;
    const [selectedSku, setSelectedSku] = useState('');
    const [bulkQuantity, setBulkQuantity] = useState('');
    const [bulkPrice, setBulkPrice] = useState('');
    const [products, setProducts] = useState([]);

    useEffect(() => {
        // Fetch available products for the dropdown
        axios.get(`${apiUrl}/products`)
            .then(response => setProducts(response.data))
            .catch(error => console.error('Error fetching products:', error));
    }, [fetchProducts]);

    const handleSubmit = (e) => {
        e.preventDefault();
        axios.post(`${apiUrl}/bulkPriceRules`, { sku: selectedSku, bulk_quantity: bulkQuantity, bulk_price: bulkPrice })
            .then(() => {
                setSelectedSku('');
                setBulkQuantity('');
                setBulkPrice('');
                fetchProducts(); // Refresh the product list
            })
            .catch(error => console.error('Error adding bulk price rule:', error));
    };

    return (
        <div>
            <h2>Configure Bulk Price Rule</h2>
            <form onSubmit={handleSubmit}>
                <div>
                    <label>Select Product: </label>
                    <select value={selectedSku} onChange={(e) => setSelectedSku(e.target.value)}>
                        <option value="">-- Select Product --</option>
                        {products.map(product => (
                            <option key={product.sku} value={product.sku}>
                                {product.sku} (Price: ${product.price})
                            </option>
                        ))}
                    </select>
                </div>
                <div>
                    <label>Bulk Quantity: </label>
                    <input
                        type="number"
                        value={bulkQuantity}
                        onChange={(e) => setBulkQuantity(e.target.value)}
                    />
                </div>
                <div>
                    <label>Bulk Price: </label>
                    <input
                        type="number"
                        value={bulkPrice}
                        onChange={(e) => setBulkPrice(e.target.value)}
                    />
                </div>
                <button type="submit">Add Bulk Price Rule</button>
            </form>
        </div>
    );
};

export default BulkPriceRules;
