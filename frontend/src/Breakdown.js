import React from 'react';
import axios from 'axios';

const Breakdown = ({ isOpen, onClose, breakdown, totalPrice, orderId, status }) => {
    const handleCompleteOrder = () => {
        axios.post(`http://localhost/api/checkout/completeOrder/${orderId}`)
            .then(response => {
                alert('Order completed successfully');
                onClose(); // Close the modal after completing the order
                // Optionally, you could also refresh the order history here
            })
            .catch(error => {
                console.error('Error completing the order:', error);
                alert('Failed to complete the order');
            });
    };

    const handleCancelOrder = () => {
        axios.post(`http://localhost/api/checkout/cancelOrder/${orderId}`)
            .then(response => {
                alert('Order canceled successfully');
                onClose(); // Close the modal after canceling the order
                // Optionally, you could also refresh the order history here
            })
            .catch(error => {
                console.error('Error canceling the order:', error);
                alert('Failed to cancel the order');
            });
    };

    if (!isOpen) return null;

    return (
        <div className="modal-overlay">
            <div className="modal-content">
                <h2>Order Breakdown</h2>
                {/* Conditional buttons based on order status */}
                {status === 'CREATED' && (
                    <div>
                        <button onClick={handleCompleteOrder}>Complete Order</button>
                        <button onClick={handleCancelOrder}>Cancel Order</button>
                    </div>
                )}
                <table>
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Regular Price</th>
                        <th>Discounted Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    {breakdown && breakdown.map((item, index) => (
                        <tr key={index}>
                            <td>{item.product}</td>
                            <td>{item.quantity}</td>
                            <td>{item.regularPrice}</td>
                            <td>
                                {item.appliedRule ?
                                    `${item.discountedPrice}` :
                                    'No Discount'}
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
                <h3>Total Price: {totalPrice}</h3>

                <button onClick={onClose}>Close</button>
            </div>
        </div>
    );
};

export default Breakdown;

