# Running

1. `docker ocmpose --profile base up` - to start RabbitMQ
2. `docker compose --profile producer up`
3. `docker compose --profile consumer up` - this can be ran multiple times to check how consumer fetches 
4. `docker compose --profile consumer2 up` - similar to above, just starts another consumer
