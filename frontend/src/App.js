import React from 'react';
import './App.css';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import ProductPage from './Products'; // Adjust based on your file structure
import OrderHistory from './OrderHistory';

const App = () => {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<ProductPage />} />
                <Route path="/order-history" element={<OrderHistory />} />
                {/* Add other routes as needed */}
            </Routes>
        </Router>
    );
};

export default App;
