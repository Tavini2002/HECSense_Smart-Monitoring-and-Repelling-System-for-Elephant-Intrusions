"""
Helper script to download demo elephant videos for testing.
This script provides instructions and can download videos from various sources.
"""

import os
import sys

def print_instructions():
    """Print instructions for downloading demo videos."""
    print("=" * 60)
    print("ELEPHANT DETECTION - DEMO VIDEO DOWNLOAD GUIDE")
    print("=" * 60)
    print("\nOption 1: Download from Pixabay (Free)")
    print("-" * 60)
    print("1. Visit: https://pixabay.com/videos/search/elephant/")
    print("2. Search for 'elephant' videos")
    print("3. Download free HD videos")
    print("4. Save them in the 'research' folder as 'input-1.mp4', 'input-2.mp4', etc.")
    print()
    
    print("Option 2: Download using yt-dlp (YouTube)")
    print("-" * 60)
    print("Install: pip install yt-dlp")
    print("Then run:")
    print('  yt-dlp -f "best[height<=720]" <youtube_url> -o input-1.mp4')
    print()
    
    print("Option 3: Use NESTLER Dataset")
    print("-" * 60)
    print("1. Visit: https://zenodo.org/records/15804949")
    print("2. Download elephant videos from the dataset")
    print("3. Extract and copy to research folder")
    print()
    
    print("Option 4: Use Roboflow Dataset")
    print("-" * 60)
    print("1. Visit: https://universe.roboflow.com/roboflow-universe-projects/elephant-detection-cxnt1")
    print("2. Sign up and download the dataset")
    print("3. Extract videos to research folder")
    print()
    
    print("=" * 60)
    print("RECOMMENDED: Use existing videos in research folder")
    print("=" * 60)
    print("\nIf you already have input-1.mp4 and input-2.mp4, you can use them directly.")
    print("Just make sure the VIDEO_SOURCE in detect_elephant_distance.py points to the correct file.")
    print()

def check_existing_videos():
    """Check if demo videos already exist."""
    research_dir = os.path.dirname(os.path.abspath(__file__))
    video_files = [f for f in os.listdir(research_dir) if f.endswith(('.mp4', '.avi', '.mov'))]
    
    if video_files:
        print("Found existing video files:")
        for video in video_files:
            filepath = os.path.join(research_dir, video)
            size_mb = os.path.getsize(filepath) / (1024 * 1024)
            print(f"  ✓ {video} ({size_mb:.1f} MB)")
        return True
    else:
        print("No video files found in research folder.")
        return False

if __name__ == "__main__":
    print("\n")
    if check_existing_videos():
        print("\nYou can use these existing videos for testing!")
    else:
        print("\nNo videos found. Follow the instructions below to download demo videos.\n")
    
    print_instructions()
    
    print("\nAfter downloading videos:")
    print("1. Place them in the 'research' folder")
    print("2. Update VIDEO_SOURCE in detect_elephant_distance.py")
    print("3. Run: python detect_elephant_distance.py")
    print()




