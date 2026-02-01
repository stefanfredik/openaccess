import requests
import json
from typing import Dict, Any

class GoogleMapsAPITester:
    def __init__(self, api_key: str):
        self.api_key = api_key
        self.results = {}
    
    def test_geocoding(self) -> Dict[str, Any]:
        """Test Geocoding API"""
        print("\n🔍 Testing Geocoding API...")
        url = f"https://maps.googleapis.com/maps/api/geocode/json"
        params = {
            'address': 'Jakarta',
            'key': self.api_key
        }
        
        try:
            response = requests.get(url, params=params)
            data = response.json()
            
            if data['status'] == 'OK':
                print("✅ Geocoding API: BERHASIL")
                print(f"   Lokasi: {data['results'][0]['formatted_address']}")
                self.results['geocoding'] = True
                return {'status': 'success', 'data': data}
            else:
                print(f"❌ Geocoding API: GAGAL - {data['status']}")
                if 'error_message' in data:
                    print(f"   Error: {data['error_message']}")
                self.results['geocoding'] = False
                return {'status': 'error', 'data': data}
        except Exception as e:
            print(f"❌ Geocoding API: ERROR - {str(e)}")
            self.results['geocoding'] = False
            return {'status': 'error', 'message': str(e)}
    
    def test_places(self) -> Dict[str, Any]:
        """Test Places API (Text Search)"""
        print("\n🏪 Testing Places API...")
        url = f"https://maps.googleapis.com/maps/api/place/textsearch/json"
        params = {
            'query': 'restaurants in Jakarta',
            'key': self.api_key
        }
        
        try:
            response = requests.get(url, params=params)
            data = response.json()
            
            if data['status'] in ['OK', 'ZERO_RESULTS']:
                print("✅ Places API: BERHASIL")
                if data['status'] == 'OK':
                    print(f"   Ditemukan {len(data['results'])} tempat")
                self.results['places'] = True
                return {'status': 'success', 'data': data}
            else:
                print(f"❌ Places API: GAGAL - {data['status']}")
                if 'error_message' in data:
                    print(f"   Error: {data['error_message']}")
                self.results['places'] = False
                return {'status': 'error', 'data': data}
        except Exception as e:
            print(f"❌ Places API: ERROR - {str(e)}")
            self.results['places'] = False
            return {'status': 'error', 'message': str(e)}
    
    def test_directions(self) -> Dict[str, Any]:
        """Test Directions API"""
        print("\n🗺️ Testing Directions API...")
        url = f"https://maps.googleapis.com/maps/api/directions/json"
        params = {
            'origin': 'Jakarta',
            'destination': 'Bandung',
            'key': self.api_key
        }
        
        try:
            response = requests.get(url, params=params)
            data = response.json()
            
            if data['status'] == 'OK':
                print("✅ Directions API: BERHASIL")
                route = data['routes'][0]['legs'][0]
                print(f"   Jarak: {route['distance']['text']}")
                print(f"   Waktu: {route['duration']['text']}")
                self.results['directions'] = True
                return {'status': 'success', 'data': data}
            else:
                print(f"❌ Directions API: GAGAL - {data['status']}")
                if 'error_message' in data:
                    print(f"   Error: {data['error_message']}")
                self.results['directions'] = False
                return {'status': 'error', 'data': data}
        except Exception as e:
            print(f"❌ Directions API: ERROR - {str(e)}")
            self.results['directions'] = False
            return {'status': 'error', 'message': str(e)}
    
    def test_distance_matrix(self) -> Dict[str, Any]:
        """Test Distance Matrix API"""
        print("\n📏 Testing Distance Matrix API...")
        url = f"https://maps.googleapis.com/maps/api/distancematrix/json"
        params = {
            'origins': 'Jakarta',
            'destinations': 'Surabaya',
            'key': self.api_key
        }
        
        try:
            response = requests.get(url, params=params)
            data = response.json()
            
            if data['status'] == 'OK':
                print("✅ Distance Matrix API: BERHASIL")
                element = data['rows'][0]['elements'][0]
                if element['status'] == 'OK':
                    print(f"   Jarak: {element['distance']['text']}")
                    print(f"   Waktu: {element['duration']['text']}")
                self.results['distance_matrix'] = True
                return {'status': 'success', 'data': data}
            else:
                print(f"❌ Distance Matrix API: GAGAL - {data['status']}")
                if 'error_message' in data:
                    print(f"   Error: {data['error_message']}")
                self.results['distance_matrix'] = False
                return {'status': 'error', 'data': data}
        except Exception as e:
            print(f"❌ Distance Matrix API: ERROR - {str(e)}")
            self.results['distance_matrix'] = False
            return {'status': 'error', 'message': str(e)}
    
    def run_all_tests(self):
        """Jalankan semua test"""
        print("=" * 60)
        print("🚀 GOOGLE MAPS API TESTER")
        print("=" * 60)
        
        self.test_geocoding()
        self.test_places()
        self.test_directions()
        self.test_distance_matrix()
        
        # Summary
        print("\n" + "=" * 60)
        print("📊 RINGKASAN HASIL TEST")
        print("=" * 60)
        passed = sum(1 for v in self.results.values() if v)
        total = len(self.results)
        print(f"Total: {passed}/{total} test berhasil")
        print("\nDetail:")
        for test, result in self.results.items():
            status = "✅ BERHASIL" if result else "❌ GAGAL"
            print(f"  - {test.replace('_', ' ').title()}: {status}")
        print("=" * 60)

def main():
    # Ganti dengan API Key Anda
    api_key = input("Masukkan Google Maps API Key: ").strip()
    
    if not api_key:
        print("❌ API Key tidak boleh kosong!")
        return
    
    tester = GoogleMapsAPITester(api_key)
    tester.run_all_tests()
    
    # Tanya apakah ingin menyimpan hasil
    save = input("\nSimpan hasil ke file JSON? (y/n): ").strip().lower()
    if save == 'y':
        filename = "gmaps_test_results.json"
        with open(filename, 'w') as f:
            json.dump(tester.results, f, indent=2)
        print(f"✅ Hasil disimpan ke {filename}")

if __name__ == "__main__":
    main()