// src/Products.js
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './Products.css';
import BulkPriceRules from './BulkPriceRules';
import CheckoutResponse from "./Checkout";
import OrderHistory from './OrderHistory';
import {useNavigate} from "react-router-dom";

const Products = () => {
    const [products, setProducts] = useState([]);
    const [sku, setSku] = useState('');
    const [price, setPrice] = useState('');
    const [isEditing, setIsEditing] = useState(false);
    const [editSku, setEditSku] = useState('');
    const [basket, setBasket] = useState([]);
    const [checkoutResponse, setCheckoutResponse] = useState(null);
    const navigate = useNavigate(); // Use useNavigate

    const handleOrderHistoryRedirect = () => {
        navigate('/order-history'); // Use navigate for redirection
    };

    // Fetch products on component mount
    useEffect(() => {
        fetchProducts();
    }, []);

    const fetchProducts = () => {
        axios.get('http://localhost/api/products')
            .then(response => setProducts(response.data))
            .catch(error => console.error('Error fetching products:', error));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (isEditing) {
            axios.put(`http://localhost/api/products/${editSku}`, { price })
                .then(() => {
                    setIsEditing(false);
                    setSku('');
                    setPrice('');
                    fetchProducts();
                })
                .catch(error => console.error('Error updating product:', error));
        } else {
            axios.post('http://localhost/api/products', { sku, price })
                .then(() => {
                    setSku('');
                    setPrice('');
                    fetchProducts();
                })
                .catch(error => console.error('Error creating product:', error));
        }
    };

    const handleEdit = (product) => {
        setIsEditing(true);
        setEditSku(product.sku);
        setSku(product.sku);
        setPrice(product.price);
    };

    const handleDelete = (sku) => {
        axios.delete(`http://localhost/api/products/${sku}`)
            .then(() => fetchProducts())
            .catch(error => console.error('Error deleting product:', error));
    };

    const handleDisableRule = (sku) => {
        axios.delete(`http://localhost/api/bulkPriceRules/${sku}`)
            .then(() => {
                // Optionally, refresh the product list after disabling the rule
                fetchProducts(); // Fetch products again to see the updated state
            })
            .catch(error => console.error('Error disabling bulk price rule:', error));
    };

    const handleAddToBasket = (sku) => {
        setBasket(prevBasket => {
            // Check if the product is already in the basket
            const existingProduct = prevBasket.find(item => item.sku === sku);
            if (existingProduct) {
                // If it exists, you might want to increment the quantity or handle it differently
                return prevBasket.map(item =>
                    item.sku === sku ? { ...item, quantity: item.quantity + 1 } : item
                );
            }
            // If it doesn't exist, add it to the basket with an initial quantity of 1
            return [...prevBasket, { sku, quantity: 1 }];
        });
    };

    const handleRemoveFromBasket = (sku) => {
        setBasket(prevBasket => {
            return prevBasket.map(item => {
                if (item.sku === sku) {
                    if (item.quantity > 1) {
                        // Decrease the quantity by one
                        return { ...item, quantity: item.quantity - 1 };
                    } else {
                        // Remove the item from the basket
                        return null; // Mark for removal
                    }
                }
                return item;
            }).filter(item => item !== null); // Filter out null items
        });
    };

    const handleCheckout = () => {
        // Format the basket for the API request
        const skus = [];
        basket.forEach(item => {
            // Add the SKU to the array according to its quantity
            for (let i = 0; i < item.quantity; i++) {
                skus.push(item.sku);
            }
        });

        // Send the request
        axios.post('http://localhost/api/checkout/scan', { skus })
            .then(response => {
                // Handle success (e.g., show a success message, clear the basket, etc.)
                console.log('Checkout successful:', response.data);
                setCheckoutResponse(response.data);
                setBasket([]); // Optionally clear the basket after checkout
            })
            .catch(error => {
                // Handle error (e.g., show an error message)
                console.error('Error during checkout:', error);
            });
    };

    const handleOrderCompleteOrCancel = () => {
        setCheckoutResponse(null); // Reset the response
    };

    return (
        <div className="products-container">
            <h1>Product Management</h1>
            <button onClick={handleOrderHistoryRedirect}>View Order History</button>
            <div className="form-container">
                <div className="product-form">
                    <h2>Product Form</h2>
                    <form onSubmit={handleSubmit}>
                        <div>
                            <label>SKU: </label>
                            <input
                                type="text"
                                value={sku}
                                onChange={(e) => setSku(e.target.value)}
                                readOnly={isEditing}
                            />
                        </div>
                        <div>
                            <label>Price: </label>
                            <input
                                type="number"
                                value={price}
                                onChange={(e) => setPrice(e.target.value)}
                            />
                        </div>
                        <button type="submit">
                            {isEditing ? 'Update Product' : 'Add Product'}
                        </button>
                    </form>
                </div>
                <div className="bulk-price-rule-form">
                    <BulkPriceRules fetchProducts={fetchProducts}/>
                </div>
            </div>
            <h2>Available Products</h2>
            <table>
                <thead>
                <tr>
                    <th>SKU</th>
                    <th>Price</th>
                    <th colSpan={2}>Bulk Price Rule</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {products.map(product => (
                    <tr key={product.sku}>
                        <td>{product.sku}</td>
                        <td>${product.price}</td>
                        {product.bulkPriceRules ? (
                            <>
                                <td>{product.bulkPriceRules.bulkQuantity}</td>
                                <td>${product.bulkPriceRules.bulkPrice}</td>
                            </>
                        ) : (
                            <td colSpan={2}>No Rule</td>
                        )}
                        <td>
                            <button onClick={() => handleEdit(product)}>Edit</button>
                            {product.bulkPriceRules && (
                                <button onClick={() => handleDisableRule(product.sku)}>Disable Rule</button>
                            )}
                            <button onClick={() => handleDelete(product.sku)}>Delete</button>
                            <button onClick={() => handleAddToBasket(product.sku)}>Add to Basket</button>
                        </td>
                    </tr>
                ))}
                </tbody>
            </table>
            <h2>Basket</h2>
            <ul>
                {basket.map(item => (
                    <li key={item.sku}>
                        {item.sku}: {item.quantity}
                        <button onClick={() => handleRemoveFromBasket(item.sku)}>Remove</button>
                    </li>
                ))}
            </ul>
            {basket.length > 0 && (
                <button onClick={handleCheckout}>Checkout</button>
            )}

            {/* Render the checkout response if available */}
            {checkoutResponse && <CheckoutResponse
                response={checkoutResponse}
                onOrderCompleteOrCancel={handleOrderCompleteOrCancel}
            />}
        </div>
    );
};

export default Products;
