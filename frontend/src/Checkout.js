import axios from "axios";

const CheckoutResponse = ({ response, onOrderCompleteOrCancel }) => {
    const apiUrl = process.env.REACT_APP_API_URL;
    const handleCompleteOrder = () => {
        axios.get(`${apiUrl}/checkout/completeOrder/${response.id}`)
            .then(res => {
                console.log('Order completed:', res.data);
                // Handle successful completion (e.g., display a message)
                onOrderCompleteOrCancel();
            })
            .catch(err => {
                console.error('Error completing order:', err);
            });
    };

    const handleCancelOrder = () => {
        axios.get(`${apiUrl}/checkout/cancelOrder/${response.id}`)
            .then(res => {
                console.log('Order canceled:', res.data);
                // Handle successful cancellation (e.g., display a message)
                onOrderCompleteOrCancel();
            })
            .catch(err => {
                console.error('Error canceling order:', err);
            });
    };

    return (
        <div>
            <h2>Checkout Summary</h2>
            <table>
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{response.id}</td>
                    <td>{response.totalPrice}</td>
                    <td>
                        <button onClick={handleCompleteOrder}>Complete Order</button>
                        <button onClick={handleCancelOrder}>Cancel Order</button>
                    </td>
                </tr>
                </tbody>
            </table>

            <h3>Discount Breakdown</h3>
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Regular Price</th>
                    <th>Discounted Price</th>
                    <th>Applied Rule</th>
                </tr>
                </thead>
                <tbody>
                {response.discountBreakdown.map((item, index) => (
                    <tr key={index}>
                        <td>{item.product}</td>
                        <td>{item.quantity}</td>
                        <td>{item.regularPrice}</td>
                        <td>
                            {item.appliedRule ?
                                `${item.discountedPrice}` :
                                'No Discount'}
                        </td>
                        <td>
                            {item.appliedRule ?
                                `${item.appliedRule.bulkQuantity} at ${item.appliedRule.bulkPrice}` :
                                'No Rule'}
                        </td>
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
};

export default CheckoutResponse;
