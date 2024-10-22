# Supermarket Checkout Simulation

This software simulates a supermarket checkout system, enabling users to scan products and calculate the total cost, including applying bulk pricing promotions. The application is built using Symfony for the backend (API) and React for the frontend.

## Features

- Scan products to calculate total prices.
- Supports bulk pricing promotions.
- Frontend interface for managing products and checkout operations.

## Prerequisites

Before running the application, make sure you have the following installed:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/Qupiter/Simple-checkout.git
cd Simple-checkout
```

### 2. Build and Run the Application

To build and run the application using Docker Compose, follow these steps:

1. **Build the Docker images**:

   ```bash
   docker-compose build
   ```

2. **Start the services**:

   ```bash
   docker-compose up
   ```

   This will start:
    - The Symfony backend (API) on `http://localhost:80`.
    - The React frontend on `http://localhost:3000`.
    - A MySQL database.

   > You can also run the services in the background by adding the `-d` option:

   ```bash
   docker-compose up -d
   ```

### 3. Access the Application

- **Backend (API)**: Access the Symfony API at `http://localhost:80`.
- **Frontend (React)**: Access the React frontend at `http://localhost:3000`.

### 4. Stopping the Services

To stop the running containers:

```bash
docker-compose down
```

To stop and remove containers, networks, and volumes:

```bash
docker-compose down -v
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
