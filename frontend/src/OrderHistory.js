import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import BreakdownModal from './Breakdown';

const OrderHistory = () => {
    const [orders, setOrders] = useState([]);
    const [selectedBreakdown, setSelectedBreakdown] = useState(null);
    const [selectedTotalPrice, setSelectedTotalPrice] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const navigate = useNavigate();
    const [selectedOrderId, setSelectedOrderId] = useState(null);
    const [selectedStatus, setSelectedStatus] = useState(null);

    useEffect(() => {
        axios.get('http://localhost/api/checkout/orderHistory')
            .then(response => {
                setOrders(response.data);
            })
            .catch(error => {
                console.error('Error fetching order history:', error);
            });
    }, []);

    const handleRedirectToProducts = () => {
        navigate('/'); // Redirect to the product page
    };

    const openModal = (breakdown, totalPrice, orderId, status) => {
        setSelectedBreakdown(breakdown);
        setSelectedTotalPrice(totalPrice); // Store the total price
        setSelectedOrderId(orderId); // Store the order ID
        setSelectedStatus(status); // Store the order status
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setSelectedBreakdown(null);
        setSelectedTotalPrice(null);
    };

    return (
        <div className="table-container">
            <h2>Order History</h2>
            <button onClick={handleRedirectToProducts}>Back to Products</button>
            <table>
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                {orders.map(order => (
                    <tr key={order.id} className="clickable-row"
                        onClick={() => openModal(order.discountBreakdown, order.totalPrice, order.id, order.status)}>
                        <td>{order.id}</td>
                        <td>{order.status}</td>
                        <td>{order.totalPrice}</td>
                        <td>{order.createdAt ? new Date(order.createdAt).toLocaleDateString() : 'N/A'}</td>
                    </tr>
                ))}
                </tbody>
            </table>

            <BreakdownModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                breakdown={selectedBreakdown}
                totalPrice={selectedTotalPrice}
                orderId={selectedOrderId}
                status={selectedStatus}
            />
        </div>
    );
};

export default OrderHistory;
