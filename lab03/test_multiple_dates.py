import subprocess
import sys
from datetime import datetime, timedelta

def test_script():
    """Test the script with multiple dates"""
    
    # Test cases with equal intervals (approximately 50 days)
    base_date = datetime(2025, 1, 1)
    test_cases = [
        ("USD", "EUR", (base_date).strftime('%Y-%m-%d')),
        ("EUR", "USD", (base_date + timedelta(days=50)).strftime('%Y-%m-%d')),
        ("USD", "MDL", (base_date + timedelta(days=100)).strftime('%Y-%m-%d')),
        ("EUR", "RON", (base_date + timedelta(days=150)).strftime('%Y-%m-%d')),
        ("USD", "EUR", (base_date + timedelta(days=200)).strftime('%Y-%m-%d')),
        ("MDL", "USD", (base_date + timedelta(days=250)).strftime('%Y-%m-%d'))
    ]
    
    print("Testing currency exchange script with multiple dates...")
    print("=" * 50)
    
    successful_tests = 0
    
    for i, (from_curr, to_curr, date) in enumerate(test_cases, 1):
        print(f"\nTest {i}: {from_curr} -> {to_curr} on {date}")
        print("-" * 40)
        
        try:
            result = subprocess.run(
                [sys.executable, "currency_exchange_rate.py", from_curr, to_curr, date],
                capture_output=True,
                text=True,
                timeout=30
            )
            
            if result.returncode == 0:
                print("[SUCCESS]")
                successful_tests += 1
            else:
                print("[FAILED]")
                if result.stderr:
                    print(f"Error: {result.stderr}")
                
        except subprocess.TimeoutExpired:
            print("[TIMEOUT]")
        except Exception as e:
            print(f"[EXCEPTION] {e}")
    
    print("\n" + "=" * 50)
    print(f"Results: {successful_tests}/{len(test_cases)} tests passed")
    
    if successful_tests == len(test_cases):
        print("ALL TESTS COMPLETED SUCCESSFULLY!")
    else:
        print("SOME TESTS FAILED. Check error.log for details.")

if __name__ == "__main__":
    test_script()