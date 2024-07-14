from locust import HttpUser, task, between

class WebsiteUser(HttpUser):
    wait_time = between(1, 5)

    def on_start(self):
        self.token = "1|WTz8or2yGshCHT35HZXKKRXUrJ9X4xNQ2PDPvVGU52f29d15"
        self.headers = {'Authorization': f'Bearer {self.token}', 'accept': 'application/json'}

    @task
    def load_test(self):
        self.client.get("/api/companies", headers=self.headers)
        # self.client.post("localhost:8000/api/companies", json={"name": "Test Company", "address": "123 Test St"})